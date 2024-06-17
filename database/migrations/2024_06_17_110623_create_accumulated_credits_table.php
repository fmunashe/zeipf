<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accumulated_credits', function (Blueprint $table) {
            $table->id();
            $table->string('ecNumber', 20);
            $table->date('valuationDate');
            $table->decimal('zwlInterest');
            $table->decimal('usdInterest');
            $table->decimal('zwlOpening');
            $table->decimal('zwlClosing');
            $table->decimal('usdOpening');
            $table->decimal('usdClosing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accumulated_credits');
    }
};
