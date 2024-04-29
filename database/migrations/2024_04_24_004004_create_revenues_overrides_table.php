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
        Schema::create('revenues_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', length: 100);
            $table->unsignedInteger('amount');
            $table->date('receiving_date')
                ->index('receiving_date_update_index');
            $table->string('description', length: 300);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('revenues_id')->references('id')->on('revenues');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues_overrides');
    }
};
