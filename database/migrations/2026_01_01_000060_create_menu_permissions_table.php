<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan hak akses menu per role ("Grup"), diatur dari Setting > Manajemen Grup & Akses.
     * Kalau tidak ada baris untuk kombinasi role+menu, sistem pakai default bawaan (lihat MenuPermission::allows()).
     * Super Admin (role=admin) tidak diatur di sini — selalu full akses.
     */
    public function up(): void
    {
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // karyawan | hr | ga
            $table->string('menu_key');
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['role', 'menu_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permissions');
    }
};
