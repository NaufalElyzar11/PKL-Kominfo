<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tampilkan profil super admin
     */
    public function index()
    {
        $user = Auth::user();
        return view('superadmin.profile.profile', compact('user'));
    }

    /**
     * Form edit profil super admin
     */
    public function edit()
    {
        $user = Auth::user();
        return view('superadmin.profile.edit', compact('user'));
    }

    /**
     * Update profil super admin
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Update nama dan email
        $user->name  = $validated['name'];
        $user->email = $validated['email'];

        // Update password jika ada input
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Upload avatar baru jika ada
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
                Storage::delete('public/avatars/' . $user->avatar);
            }

            $filename = time() . '_' . $request->file('avatar')->getClientOriginalName();
            $request->file('avatar')->storeAs('public/avatars', $filename);
            $user->avatar = $filename;
        }

        $user->save();

        return redirect()
            ->route('super.profile.index') // Sesuaikan dengan route yang benar
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
