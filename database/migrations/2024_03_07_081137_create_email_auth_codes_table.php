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
        Schema::create('email_auth_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('auth_code');
            $table->timestampTz('created_at');
            $table->timestampTz('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_auth_codes');
    }
};
