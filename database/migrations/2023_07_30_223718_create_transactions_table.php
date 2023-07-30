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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('price');
            $table->enum('type', ['entry', 'exit']);
            $table->boolean('is_owner');
            $table->date('date');
            $table->date('pay_month')->comment('Stores the month the transaction belongs to, not the date the transaction was made');
            $table->boolean('paid_out')->default(0);
            $table->uuid('user_id')->nullable(false);
            $table->unsignedInteger('card_id');
            $table->unsignedInteger('category_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('card_id')->references('id')->on('cards');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
