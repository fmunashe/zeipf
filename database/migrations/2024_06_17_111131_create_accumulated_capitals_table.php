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
        Schema::create('accumulated_capitals', function (Blueprint $table) {
            $table->id();
            $table->string('national_id', 20);
            $table->integer('year');
            $table->decimal('Jan');
            $table->decimal('Feb');
            $table->decimal('Mar');
            $table->decimal('Apr');
            $table->decimal('May');
            $table->decimal('Jun');
            $table->decimal('Jul');
            $table->decimal('Aug');
            $table->decimal('Sep');
            $table->decimal('Oct');
            $table->decimal('Nov');
            $table->decimal('Dec');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accumulated_capitals');
    }
};
