<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code', 64)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('birth_date');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender', 10);
            $table->string('civil_status', 20);
            $table->string('contact_number', 15)->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('blood_type', 5)->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_number', 15)->nullable();
            $table->text('allergies')->nullable();
            $table->text('existing_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
