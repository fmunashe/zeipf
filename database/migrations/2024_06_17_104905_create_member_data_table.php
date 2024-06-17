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
        Schema::create('member_data', function (Blueprint $table) {
            $table->id();
            $table->string("national_id", 20)->unique();
            $table->string("name", 20);
            $table->string("surname", 20);
            $table->date("dob");
            $table->date("doj");
            $table->date("doe")->nullable();
            $table->enum("memberType", ['active', 'frozen', 'pensioner']);
            $table->enum("memberStatus", ['active', 'suspended']);
            $table->string("pin", 20)->nullable();
            $table->enum("memberCategory", ['widow', 'pensioner', 'student', 'child', 'contributory', 'non-contributory']);
            $table->string("ecNumber", 20);
            $table->string("lifeStatus", 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_data');
    }
};
