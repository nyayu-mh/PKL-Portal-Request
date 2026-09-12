<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jabatan_ttf', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->string('divisi')->nullable();
            // PIC HR yang bertanggung jawab merekrut untuk jabatan ini
            $table->foreignId('pic_hr_user_id')->nullable()->constrained('users')->nullOnDelete();
            // Target Timeline Fulfillment dalam hari kerja
            $table->unsignedInteger('target_hari_kerja')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jabatan_ttf');
    }
};
