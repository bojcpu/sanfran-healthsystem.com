<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasUnsupportedRecipients = DB::table('notifications')
            ->where('notifiable_type', '!=', User::class)
            ->exists();
        $hasOrphanedRecipients = DB::table('notifications')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'notifications.notifiable_id');
            })
            ->exists();

        if ($hasUnsupportedRecipients || $hasOrphanedRecipients) {
            throw new RuntimeException('Notifications contain recipients that cannot be linked to users.');
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::table('notifications')->update([
            'user_id' => DB::raw('notifiable_id'),
        ]);

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->nullableMorphs('notifiable');
        });

        DB::table('notifications')
            ->whereNotNull('user_id')
            ->update([
                'notifiable_type' => User::class,
                'notifiable_id' => DB::raw('user_id'),
            ]);

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
