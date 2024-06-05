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

        Schema::table('cages', function (Blueprint $table) {
            $table->dropForeign(['reptile_serial_code']);
        });


        Schema::table('reptiles', function (Blueprint $table) {
            $table->dropUnique('serial_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reptiles', function (Blueprint $table) {
            $table->unique('serial_code');
        });

        Schema::table('cages', function (Blueprint $table) {
            $table->foreign('reptile_serial_code')->references('serial_code')->on('reptiles')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }
};
