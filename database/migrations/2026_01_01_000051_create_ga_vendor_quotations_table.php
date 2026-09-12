<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ga_vendor_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ga_request_id')->constrained('ga_requests')->cascadeOnDelete();
            $table->string('nama_vendor');
            $table->string('file_path');
            $table->decimal('harga_penawaran', 15, 2)->nullable();
            $table->boolean('is_terpilih')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ga_vendor_quotations');
    }
};
