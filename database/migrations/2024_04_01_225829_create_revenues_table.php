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
            $table->string('title');
            $table->unsignedInteger('amount');
            $table->date('date');
            $table->date('receiving_date')->comment('This column represents the month in which the money will be considered when calculating income versus expenses.');
            $table->boolean('recurrent');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->foreignUuid('category_id')->references('id')->on('categories');
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
