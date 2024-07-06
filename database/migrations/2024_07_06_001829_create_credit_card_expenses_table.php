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
        Schema::create('card_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('total_amount');
            $table->boolean('is_owner')->default(true);
            $table->timestamps();

            $table->foreignUuid('user_id')->index()->references('id')->on('users');
            $table->foreignUuid('card_id')->nullable()->references('id')->on('cards');
            $table->foreignUuid('category_id')->nullable()->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_card_expenses');
    }
};
