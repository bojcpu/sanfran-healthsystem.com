<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maternal_care_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('para')->nullable();
            $table->date('last_menstrual_period')->nullable();
            $table->string('spouse_name')->nullable();
            $table->unsignedTinyInteger('living_children')->nullable();
            $table->string('birth_plan')->nullable();
            $table->string('hospital')->nullable();
            $table->string('rhu')->nullable();
            $table->string('lgc')->nullable();
            $table->string('birth_attendant')->nullable();
            $table->string('pulse_rate', 20)->nullable();
            $table->string('respiratory_rate', 20)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->json('present_history')->nullable();
            $table->text('present_history_other')->nullable();
            $table->json('past_history')->nullable();
            $table->text('past_history_other')->nullable();
            $table->json('family_history')->nullable();
            $table->text('family_history_other')->nullable();
            $table->json('past_health_history')->nullable();
            $table->text('past_health_history_other')->nullable();
            $table->json('conjunctiva_findings')->nullable();
            $table->json('neck_findings')->nullable();
            $table->json('breast_findings')->nullable();
            $table->json('abdomen_findings')->nullable();
            $table->json('vaginal_findings')->nullable();
            $table->text('vaginal_findings_other')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('maternal_care_records', function (Blueprint $table) {
            $table->dropColumn([
                'para', 'last_menstrual_period', 'spouse_name', 'living_children',
                'birth_plan', 'hospital', 'rhu', 'lgc', 'birth_attendant',
                'pulse_rate', 'respiratory_rate', 'temperature', 'height',
                'present_history', 'present_history_other', 'past_history', 'past_history_other',
                'family_history', 'family_history_other', 'past_health_history', 'past_health_history_other',
                'conjunctiva_findings', 'neck_findings', 'breast_findings', 'abdomen_findings',
                'vaginal_findings', 'vaginal_findings_other',
            ]);
        });
    }
};
