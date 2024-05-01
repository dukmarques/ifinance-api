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
        Schema::create('revenues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', length: 100);
            $table->unsignedInteger('amount');
            $table->date('receiving_date')
                ->index('receiving_date_index')
                ->comment('This column represents the month in which the money will be considered when calculating income versus expenses.');
            $table->date('deprecated_date')->nullable();
            $table->boolean('recurrent');
            $table->string('description', length: 300);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->foreignUuid('category_id')->nullable()->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
