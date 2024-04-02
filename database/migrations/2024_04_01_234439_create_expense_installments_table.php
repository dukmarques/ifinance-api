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
            $table->integer('amount');
            $table->boolean('paid')->default(false);
            $table->unsignedInteger('parcel_number');
            $table->date('pay_month')->comment('Stores the month the transaction belongs to, not the date the transaction was made');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('expenses_id')->references('id')->on('expenses');
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
