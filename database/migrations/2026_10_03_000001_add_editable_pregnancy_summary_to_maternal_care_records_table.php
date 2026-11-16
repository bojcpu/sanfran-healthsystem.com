<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maternal_care_records', function (Blueprint $table) {
            $table->string('trimester', 20)->nullable();
            $table->unsignedTinyInteger('gestational_weeks')->nullable();
            $table->unsignedTinyInteger('gestational_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('maternal_care_records', function (Blueprint $table) {
            $table->dropColumn(['trimester', 'gestational_weeks', 'gestational_days']);
        });
    }
};
