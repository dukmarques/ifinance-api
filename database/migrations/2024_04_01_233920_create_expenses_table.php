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
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 100);
            $table->enum('type', ['simple', 'recurrent', 'installments'])->default('simple')->index();
            $table->unsignedInteger('total_amount');
            $table->boolean('is_owner');
            $table->boolean('paid')->default(false);
            $table->date('payment_month')->index();
            $table->date('deprecated_date')->nullable()->index();
            $table->string('description', 300)->nullable();
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
        Schema::dropIfExists('expenses');
    }
};
