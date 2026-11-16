<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('healthcare_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 100);
            $table->string('report_period', 100);
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedInteger('total_patients')->default(0);
            $table->unsignedInteger('total_consultations')->default(0);
            $table->unsignedInteger('total_immunizations')->default(0);
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->string('submission_status', 50)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('healthcare_reports');
    }
};
