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
        Schema::create('reptile_sale_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('reptile_sale_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('content');
            $table->unsignedBigInteger('group_comment_id');
            $table->unsignedBigInteger('parent_comment_id');
            $table->unsignedSmallInteger('depth_no')->default(0);
            $table->unsignedSmallInteger('order_no')->default(1);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reptile_sale_comments');
    }
};
