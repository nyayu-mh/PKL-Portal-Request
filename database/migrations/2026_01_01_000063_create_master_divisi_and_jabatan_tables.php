<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Referensi umum struktur organisasi (dari chart organisasi perusahaan) — daftar Divisi
     * dan daftar Jabatan/posisi. Terpisah dari master_jabatan_ttf (khusus keperluan ERF).
     * Sengaja tidak menyimpan nama orang, cuma nama posisinya.
     */
    public function up(): void
    {
        Schema::create('master_divisi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_divisi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('master_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->foreignId('master_divisi_id')->nullable()->constrained('master_divisi')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jabatan');
        Schema::dropIfExists('master_divisi');
    }
};
