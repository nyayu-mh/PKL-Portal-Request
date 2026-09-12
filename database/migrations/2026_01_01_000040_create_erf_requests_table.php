<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erf_requests', function (Blueprint $table) {
            $table->id();
            $table->string('erf_id')->unique(); // ERF-2026-0001

            // Pemohon
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_request');

            // Jenis & data umum
            $table->enum('jenis_erf', ['karyawan_baru', 'pengganti_karyawan_lama']);
            $table->unsignedInteger('jumlah_karyawan');
            $table->foreignId('jabatan_dibutuhkan_id')->nullable()->constrained('master_jabatan_ttf')->nullOnDelete();
            $table->text('uraian_tugas');
            $table->text('kualifikasi_kandidat');
            $table->text('catatan')->nullable();

            // Khusus jenis = pengganti_karyawan_lama
            $table->foreignId('karyawan_diganti_id')->nullable()->constrained('master_karyawan')->nullOnDelete();
            $table->text('alasan_penggantian')->nullable();

            // Approval atasan langsung di aplikasi (menggantikan lampiran approval manual)
            $table->enum('approval_status', ['tidak_perlu', 'menunggu', 'disetujui', 'ditolak'])->default('tidak_perlu');
            $table->foreignId('atasan_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_approval_atasan')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Otomatis dari sistem
            $table->foreignId('pic_hr_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('ttf_hari')->nullable();
            $table->date('tanggal_mencari_kandidat')->nullable();
            $table->date('estimasi_tanggal_fulfillment')->nullable();

            // Diisi/diupdate HR
            $table->enum('status', [
                'pending', 'in_review', 'approved', 'on_progress', 'interview_hr',
                'interview_user', 'offering', 'kandidat_fix', 'completed', 'on_hold', 'rejected',
            ])->default('pending');
            $table->date('tanggal_selesai')->nullable();
            $table->date('tanggal_karyawan_masuk')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erf_requests');
    }
};
