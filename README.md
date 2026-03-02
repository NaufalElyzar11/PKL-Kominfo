# 📅 SIAPCUTI - Sistem Informasi Manajemen Cuti Pegawai

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-2D3441?style=for-the-badge&logo=alpinedotjs&logoColor=white)

SIAPCUTI adalah aplikasi berbasis web untuk digitalisasi dan tata kelola pengajuan cuti pegawai secara terintegrasi pada **Dinas Komunikasi dan Informatika (Diskominfo) Kota Banjarbaru**. 

Sistem ini dirancang sebagai lapisan **validasi internal (filter operasional)** sebelum data cuti diinput ke dalam sistem kepegawaian terpusat pemerintah (BAGAWI), guna memastikan keseimbangan beban kerja dan keakuratan kuota cuti pegawai.

---

## ✨ Fitur Utama

- 📝 **Pengajuan Cuti Online (Paperless):** Pegawai dapat mengajukan cuti, memilih rentang tanggal, dan menunjuk pegawai delegasi pengganti secara mandiri.
- 🚦 **Validasi Pintar (Smart Validation):** - Terintegrasi dengan **Day Off API** untuk otomatis mengabaikan hari libur nasional dan akhir pekan (Sabtu-Minggu) dalam perhitungan jumlah hari cuti.
  - Pencegahan *Double Booking* (Bentrok jadwal) pengajuan cuti di tanggal yang sama.
- ⚡ **Approval Berjenjang:** Alur persetujuan terstruktur dari **Atasan Langsung** hingga persetujuan final oleh **Pejabat / Kepala Dinas**.
- 📊 **Monitoring & Rekapitulasi Otomatis:** Laporan riwayat dan sisa kuota cuti yang dihitung secara *real-time*. Mendukung *Export* laporan ke format **PDF (DomPDF)** dan **Excel (Maatwebsite)**.
- 🔔 **Notifikasi Real-Time:** Memberikan informasi langsung kepada user mengenai status pengajuan (Menunggu, Revisi Delegasi, Disetujui, Ditolak).

---

## 👥 Hak Akses (Role)

Sistem ini memiliki 4 level pengguna (Multi-Role):
1. **Pegawai**: Mengajukan cuti, mengecek sisa kuota, dan melihat riwayat persetujuan pribadi.
2. **Atasan Langsung**: Memantau ketersediaan staf di unit kerjanya, menyetujui delegasi tugas, dan memberikan persetujuan tahap pertama (Tahap 1).
3. **Pejabat (Kadis)**: Memonitor rekapitulasi cuti dinas dan memberikan persetujuan final (Tahap 2).
4. **Admin**: Mengelola master data pegawai, unit kerja, pengaturan kuota awal, dan mengunduh laporan rekapitulasi bulanan/tahunan.

---

## 🛠️ Teknologi yang Digunakan

- **Backend:** PHP 8.x, Laravel Framework
- **Frontend:** HTML5, CSS3, Tailwind CSS, Alpine.js
- **Database:** MySQL
- **Libraries/Packages:**
  - `barryvdh/laravel-dompdf` (Laporan PDF)
  - `maatwebsite/excel` (Laporan Excel)
  - `sweetalert2` (Pop-up Notifications)
  - `flatpickr` (Datepicker Interaktif)

---

## 🚀 Panduan Instalasi (Local Development)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di *local environment* Anda.

### Persyaratan Sistem
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL Server (Laragon / XAMPP)

### Langkah Instalasi

1. **Clone Repositori**
   ```bash
   git clone https://github.com/NaufalElyzar11/PKL-Kominfo/tree/main
   cd siapcuti
