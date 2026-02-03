<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordWaController extends Controller
{
    protected FonnteService $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Tampilkan form request reset password
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password-request');
    }

    /**
     * Proses request reset password dan kirim OTP via WhatsApp
     */
    public function sendResetToken(Request $request)
    {
        $request->validate([
            'login_identifier' => 'required|string',
        ], [
            'login_identifier.required' => 'NIP atau Nama Lengkap wajib diisi.',
        ]);

        $identifier = $request->login_identifier;

        // Cari user berdasarkan NIP atau Nama
        $user = User::where('nip', $identifier)
            ->orWhere('name', $identifier)
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors(['login_identifier' => 'Data tidak ditemukan. Pastikan NIP atau Nama sudah benar.']);
        }

        // Cek apakah user punya nomor telepon
        if (empty($user->telepon)) {
            return back()
                ->withInput()
                ->withErrors(['login_identifier' => 'Akun ini tidak memiliki nomor telepon terdaftar. Silakan hubungi Admin.']);
        }

        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan ke database (hapus yang lama jika ada)
        DB::table('password_reset_wa_tokens')->updateOrInsert(
            ['telepon' => $user->telepon],
            [
                'token' => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]
        );

        // Kirim OTP via WhatsApp
        $result = $this->fonnteService->sendPasswordResetOtp(
            $user->telepon,
            $otp,
            $user->name
        );

        if (!$result['success']) {
            return back()
                ->withInput()
                ->withErrors(['login_identifier' => 'Gagal mengirim kode OTP. Silakan coba lagi nanti.']);
        }

        // Redirect ke halaman verifikasi dengan nomor telepon tersamarkan
        $maskedPhone = $this->maskPhoneNumber($user->telepon);

        return redirect()
            ->route('password.verify')
            ->with([
                'telepon' => $user->telepon,
                'masked_phone' => $maskedPhone,
                'success' => "Kode OTP telah dikirim ke WhatsApp {$maskedPhone}",
            ]);
    }

    /**
     * Tampilkan form verifikasi OTP
     */
    public function showVerifyForm(Request $request)
    {
        // Cek apakah ada session telepon
        if (!session('telepon') && !$request->has('telepon')) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Silakan mulai proses reset password dari awal.']);
        }

        return view('auth.forgot-password-verify', [
            'telepon' => session('telepon') ?? $request->telepon,
            'masked_phone' => session('masked_phone') ?? $this->maskPhoneNumber($request->telepon),
        ]);
    }

    /**
     * Verifikasi kode OTP
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'telepon' => 'required|string',
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus 6 digit.',
        ]);

        $telepon = $request->telepon;
        $otp = $request->otp;

        // Cari token di database
        $tokenRecord = DB::table('password_reset_wa_tokens')
            ->where('telepon', $telepon)
            ->first();

        if (!$tokenRecord) {
            return back()
                ->withInput()
                ->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }

        // Cek expired (15 menit)
        $createdAt = Carbon::parse($tokenRecord->created_at);
        $expiryMinutes = config('fonnte.token_expiry', 15);

        if ($createdAt->addMinutes($expiryMinutes)->isPast()) {
            // Hapus token expired
            DB::table('password_reset_wa_tokens')->where('telepon', $telepon)->delete();

            return back()
                ->withInput()
                ->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.']);
        }

        // Verifikasi OTP
        if (!Hash::check($otp, $tokenRecord->token)) {
            return back()
                ->withInput()
                ->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        // Generate reset token untuk halaman reset password
        $resetToken = Str::random(64);

        // Update token dengan reset token
        DB::table('password_reset_wa_tokens')
            ->where('telepon', $telepon)
            ->update(['token' => Hash::make($resetToken)]);

        return redirect()
            ->route('password.reset')
            ->with([
                'telepon' => $telepon,
                'reset_token' => $resetToken,
            ]);
    }

    /**
     * Tampilkan form reset password
     */
    public function showResetForm(Request $request)
    {
        if (!session('reset_token')) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Silakan mulai proses reset password dari awal.']);
        }

        return view('auth.forgot-password-reset', [
            'telepon' => session('telepon'),
            'reset_token' => session('reset_token'),
        ]);
    }

    /**
     * Proses reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'telepon' => 'required|string',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $telepon = $request->telepon;
        $resetToken = $request->reset_token;

        // Verifikasi reset token
        $tokenRecord = DB::table('password_reset_wa_tokens')
            ->where('telepon', $telepon)
            ->first();

        if (!$tokenRecord || !Hash::check($resetToken, $tokenRecord->token)) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Token tidak valid. Silakan mulai proses reset password dari awal.']);
        }

        // Update password user
        $user = User::where('telepon', $telepon)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'User tidak ditemukan.']);
        }

        $user->password = $request->password;
        $user->save();

        // Hapus token
        DB::table('password_reset_wa_tokens')->where('telepon', $telepon)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }

    /**
     * Kirim ulang OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'telepon' => 'required|string',
        ]);

        $user = User::where('telepon', $request->telepon)->first();

        if (!$user) {
            return back()->withErrors(['error' => 'Nomor telepon tidak terdaftar.']);
        }

        // Generate OTP baru
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Update di database
        DB::table('password_reset_wa_tokens')->updateOrInsert(
            ['telepon' => $user->telepon],
            [
                'token' => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]
        );

        // Kirim OTP via WhatsApp
        $result = $this->fonnteService->sendPasswordResetOtp(
            $user->telepon,
            $otp,
            $user->name
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => 'Gagal mengirim kode OTP. Silakan coba lagi.']);
        }

        return back()->with('success', 'Kode OTP baru telah dikirim.');
    }

    /**
     * Mask nomor telepon untuk privasi
     */
    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        $visible = 4;
        $masked = str_repeat('*', $length - $visible);
        return $masked . substr($phone, -$visible);
    }
}
