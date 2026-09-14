<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('akses_wookey_weight')->default(false)->after('atasan_id');
            $table->boolean('akses_so_honey')->default(false)->after('akses_wookey_weight');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['akses_wookey_weight', 'akses_so_honey']);
        });
    }
};
