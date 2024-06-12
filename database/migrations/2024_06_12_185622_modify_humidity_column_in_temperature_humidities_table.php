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
        Schema::table('temperature_humidities', function (Blueprint $table) {
            $table->float('humidity')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temperature_humidities', function (Blueprint $table) {
            $table->unsignedSmallInteger('humidity')->change();
        });
    }
};
