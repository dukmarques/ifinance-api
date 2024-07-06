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
        Schema::create('card_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 300);
            $table->unsignedInteger('amount');
            $table->boolean('paid')->default(false);
            $table->unsignedInteger('installment_number')->index();
            $table->date('pay_month')->index();
            $table->string('notes', 300)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('card_expenses_id')->index()->references('id')->on('card_expenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_installments');
    }
};
