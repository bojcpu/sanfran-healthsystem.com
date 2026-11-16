<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maternal_care_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('pregnancy_number')->default(1);
            $table->date('expected_delivery_date')->nullable();
            $table->date('visit_date');
            $table->string('weight', 20)->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maternal_care_records');
    }
};
