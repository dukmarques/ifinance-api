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
        Schema::create('expense_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('amount');
            $table->boolean('paid')->default(false);
            $table->unsignedInteger('installment_number');
            $table->date('payment_month')->index();
            $table->string('notes', length: 300)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('expense_id')->index()->references('id')->on('expenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_installments');
    }
};
