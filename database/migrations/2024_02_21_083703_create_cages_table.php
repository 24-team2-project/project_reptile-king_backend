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
        Schema::create('cages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('reptile_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->text('memo')->nullable(true);
            $table->float('set_temp')->nullable(true);
            $table->unsignedSmallInteger('set_hum')->nullable(true);
            $table->string('serial_code', 20);
            $table->json('img_urls')->nullable(true);
            $table->timestampsTz();
            $table->timestampTz('expired_at')->nullable(true);

            $table->foreign('serial_code')->references('serial_code')->on('cage_serial_codes')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cages');
    }
};
