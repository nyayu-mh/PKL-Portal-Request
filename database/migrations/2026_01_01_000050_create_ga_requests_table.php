<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ga_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ga_id')->unique(); // GA-2026-0001

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_request');

            $table->enum('jenis_request', ['maintenance', 'pengadaan_barang', 'office_acquisition', 'renovasi_kantor']);
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('lokasi_kerja', [
                'head_office', 'crm_office', 'pdn_office', 'gudang_palembang', 'gudang_jakarta', 'gudang_pekalongan',
            ]);
            $table->string('lampiran_bukti_kondisi')->nullable(); // wajib tampil jika jenis = maintenance
            $table->string('lampiran_rekomendasi_vendor')->nullable(); // opsional dari pemohon
            $table->text('catatan')->nullable();

            // Approval atasan langsung di aplikasi (menggantikan lampiran approval manual)
            $table->enum('approval_status', ['tidak_perlu', 'menunggu', 'disetujui', 'ditolak'])->default('tidak_perlu');
            $table->foreignId('atasan_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_approval_atasan')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Otomatis dari sistem
            $table->foreignId('pic_ga_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Diisi/diupdate GA
            $table->boolean('butuh_vendor')->nullable();
            $table->string('nama_vendor')->nullable();
            $table->text('catatan_rab')->nullable();
            $table->date('tanggal_mulai_proses')->nullable();
            $table->enum('status', [
                'pending', 'diproses_ga', 'vendor_diorder', 'proses_vendor', 'barang_diterima',
                'selesai', 'ditolak', 'dibatalkan',
            ])->default('pending');
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('realisasi_biaya', 15, 2)->nullable();
            $table->text('catatan_internal')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ga_requests');
    }
};
