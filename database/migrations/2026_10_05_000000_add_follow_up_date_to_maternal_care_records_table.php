<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maternal_care_records', function (Blueprint $table) {
            $table->date('follow_up_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('maternal_care_records', function (Blueprint $table) {
            $table->dropColumn('follow_up_date');
        });
    }
};