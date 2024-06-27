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
        Schema::create('expenses_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 100)->nullable();
            $table->unsignedInteger('total_amount')->nullable();
            $table->string('description', 300)->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->date('payment_month')->index();
            $table->timestamps();

            $table->foreignUuid('expense_id')->index()->references('id')->on('expenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses_overrides');
    }
};
