<?php

namespace Database\Seeders;

use App\Models\ErfRequest;
use App\Models\GaRequest;
use App\Models\KalenderKerja;
use App\Models\MasterJabatanTtf;
use App\Models\MasterKaryawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Data awal (seed) untuk Portal Request BTC.
     * Password default SEMUA akun contoh di bawah ini: password
     * (Segera ganti password setelah login pertama kali / lewat menu Manajemen User)
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@brilliantthinkcenter.com',
            'password' => Hash::make('password'),
            'divisi' => 'IT',
            'jabatan' => 'System Administrator',
            'jabatan_level' => 'manager',
            'role' => 'admin',
        ]);

        $hr = User::create([
            'name' => 'Tim HR',
            'email' => 'hr@brilliantthinkcenter.com',
            'password' => Hash::make('password'),
            'divisi' => 'Human Resources',
            'jabatan' => 'HR Recruitment',
            'jabatan_level' => 'staff',
            'role' => 'hr',
        ]);

        $ga = User::create([
            'name' => 'Gerry Anda',
            'email' => 'ga@brilliantthinkcenter.com',
            'password' => Hash::make('password'),
            'divisi' => 'General Affair',
            'jabatan' => 'Staff General Affair',
            'jabatan_level' => 'staff',
            'role' => 'ga',
        ]);

        $manager = User::create([
            'name' => 'Contoh Manager',
            'email' => 'manager@brilliantthinkcenter.com',
            'password' => Hash::make('password'),
            'divisi' => 'Technology',
            'jabatan' => 'IT Manager',
            'jabatan_level' => 'manager',
            'role' => 'karyawan',
        ]);

        $leader = User::create([
            'name' => 'Contoh Leader',
            'email' => 'leader@brilliantthinkcenter.com',
            'password' => Hash::make('password'),
            'divisi' => 'Technology',
            'jabatan' => 'IT Team Leader',
            'jabatan_level' => 'leader',
            'role' => 'karyawan',
            'atasan_id' => $manager->id,
        ]);

        User::create([
            'name' => 'Contoh Staff',
            'email' => 'staff@brilliantthinkcenter.com',
            'password' => Hash::make('password'),
            'divisi' => 'Technology',
            'jabatan' => 'Staff IT',
            'jabatan_level' => 'staff',
            'role' => 'karyawan',
        ]);

        // Master Jabatan & TTF (contoh)
        $jabatanIt = MasterJabatanTtf::create([
            'nama_jabatan' => 'Staff IT Support',
            'divisi' => 'Technology',
            'pic_hr_user_id' => $hr->id,
            'target_hari_kerja' => 14,
        ]);

        MasterJabatanTtf::create([
            'nama_jabatan' => 'Staff Finance',
            'divisi' => 'Finance',
            'pic_hr_user_id' => $hr->id,
            'target_hari_kerja' => 21,
        ]);

        MasterJabatanTtf::create([
            'nama_jabatan' => 'Customer Relationship Officer',
            'divisi' => 'CRM',
            'pic_hr_user_id' => $hr->id,
            'target_hari_kerja' => 10,
        ]);

        // Master Data Karyawan (contoh, untuk dropdown "Karyawan yang Diganti")
        MasterKaryawan::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@brilliantthinkcenter.com',
            'divisi' => 'Technology',
            'jabatan' => 'Staff IT Support',
        ]);

        MasterKaryawan::create([
            'nama' => 'Siti Aminah',
            'email' => 'siti.aminah@brilliantthinkcenter.com',
            'divisi' => 'Finance',
            'jabatan' => 'Staff Finance',
        ]);

        // Kalender Kerja (contoh — Admin/HR wajib melengkapi kalender libur nasional & cuti bersama tahun berjalan)
        KalenderKerja::create([
            'tanggal' => now()->startOfYear()->toDateString(),
            'keterangan' => 'Tahun Baru Masehi (contoh data, lengkapi kalender resmi tahun berjalan)',
            'tipe' => 'libur',
        ]);

        // Contoh data transaksi (boleh dihapus setelah testing)
        ErfRequest::create([
            'erf_id' => 'ERF-'.now()->year.'-0001',
            'user_id' => $leader->id,
            'tanggal_request' => now()->subDays(5),
            'jenis_erf' => 'karyawan_baru',
            'jumlah_karyawan' => 1,
            'jabatan_dibutuhkan_id' => $jabatanIt->id,
            'uraian_tugas' => 'Menangani troubleshooting perangkat & jaringan kantor.',
            'kualifikasi_kandidat' => 'Min. D3 Teknik Informatika, pengalaman 1 tahun.',
            'pic_hr_user_id' => $hr->id,
            'ttf_hari' => $jabatanIt->target_hari_kerja,
            'tanggal_mencari_kandidat' => now()->subDays(4),
            'estimasi_tanggal_fulfillment' => now()->addDays(9),
            'status' => 'in_review',
            'approval_status' => 'disetujui',
            'atasan_user_id' => $manager->id,
            'approved_at' => now()->subDays(5),
        ]);

        GaRequest::create([
            'ga_id' => 'GA-'.now()->year.'-0001',
            'user_id' => $leader->id,
            'tanggal_request' => now()->subDays(3),
            'jenis_request' => 'maintenance',
            'judul' => 'AC Ruang Meeting Lantai 2 Tidak Dingin',
            'deskripsi' => 'AC di ruang meeting lantai 2 sudah 2 hari tidak dingin, mohon dicek teknisi.',
            'lokasi_kerja' => 'head_office',
            'pic_ga_user_id' => $ga->id,
            'status' => 'pending',
            'approval_status' => 'menunggu',
            'atasan_user_id' => $manager->id,
        ]);
    }
}
