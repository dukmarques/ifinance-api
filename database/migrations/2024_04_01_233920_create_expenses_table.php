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
            $table->string('title');
            $table->integer('full_amount');
            $table->boolean('is_owner');
            $table->date('date');
            $table->boolean('fully_paid')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->foreignUuid('card_id')->references('id')->on('cards');
            $table->foreignUuid('category_id')->references('id')->on('categories');
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
