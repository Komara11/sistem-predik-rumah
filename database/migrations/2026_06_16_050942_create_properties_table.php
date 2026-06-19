<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('luas_tanah');
            $table->integer('luas_bangunan');
            $table->integer('kmr_tidur');
            $table->integer('kmr_mandi');
            $table->integer('usia');
            $table->string('lokasi');
            $table->string('tipe_properti');
            $table->string('kondisi');
            $table->boolean('ada_garasi')->default(false);
            $table->bigInteger('harga');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
