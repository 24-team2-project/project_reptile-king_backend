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
        Schema::create('reptiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('serial_code', 20)->unique();
            $table->string('species');
            $table->string('gender');
            $table->date('birth')->nullable(true);
            $table->string('name');
            $table->text('memo')->nullable(true);
            $table->json('img_urls')->nullable(true);
            $table->timestampsTz();
            $table->timestampTz('expired_at')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reptiles');
    }
};
