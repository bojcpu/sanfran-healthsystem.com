<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'administrator')
            ->update(['role' => 'system_admin']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'system_admin')
            ->update(['role' => 'administrator']);
    }
};
