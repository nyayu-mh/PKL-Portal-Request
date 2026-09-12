<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kalender_kerja', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan')->nullable();
            // libur = hari ini DIANGGAP LIBUR walau hari kerja biasa (mis. cuti bersama, hari raya)
            // kerja = hari ini DIANGGAP HARI KERJA walau weekend (mis. Sabtu masuk ganti libur)
            $table->enum('tipe', ['libur', 'kerja'])->default('libur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_kerja');
    }
};
