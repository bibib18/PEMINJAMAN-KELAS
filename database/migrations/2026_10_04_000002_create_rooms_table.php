<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('gedung', 100);
            $table->unsignedTinyInteger('lantai')->default(1);
            $table->unsignedSmallInteger('kapasitas');
            $table->string('fasilitas')->nullable();
            $table->string('status', 20)->default('tersedia'); // tersedia | perawatan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
