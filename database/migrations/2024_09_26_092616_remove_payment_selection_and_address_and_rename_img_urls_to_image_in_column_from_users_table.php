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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_selection');
            $table->dropColumn('address');
            $table->renameColumn('img_urls', 'image')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('address')->nullable(true);
            $table->json('payment_selection')->nullable(true);
            $table->renameColumn('image', 'img_urls');
        });
    }
};
