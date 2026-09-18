<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ganti 2 checkbox independen (akses_wookey_weight, akses_so_honey) jadi 1 kolom
     * pilihan tunggal "brand": wookey_wight | so_honey_jr | semua.
     * Data lama dipindahkan dulu sebelum kolom boolean-nya dihapus — tidak ada data yang hilang.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('atasan_id');
        });

        DB::table('users')->where('akses_wookey_weight', true)->where('akses_so_honey', true)->update(['brand' => 'semua']);
        DB::table('users')->where('akses_wookey_weight', true)->where('akses_so_honey', false)->update(['brand' => 'wookey_wight']);
        DB::table('users')->where('akses_so_honey', true)->where('akses_wookey_weight', false)->update(['brand' => 'so_honey_jr']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['akses_wookey_weight', 'akses_so_honey']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('akses_wookey_weight')->default(false)->after('atasan_id');
            $table->boolean('akses_so_honey')->default(false)->after('akses_wookey_weight');
        });

        DB::table('users')->where('brand', 'wookey_wight')->update(['akses_wookey_weight' => true]);
        DB::table('users')->where('brand', 'so_honey_jr')->update(['akses_so_honey' => true]);
        DB::table('users')->where('brand', 'semua')->update(['akses_wookey_weight' => true, 'akses_so_honey' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
