<?php

use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SOURCE_COLUMNS = [
        Announcement::class => ['announcement_id', 'announcements'],
        CheckupRecord::class => ['checkup_record_id', 'checkup_records'],
        ImmunizationRecord::class => ['immunization_record_id', 'immunization_records'],
        MaternalCareRecord::class => ['maternal_care_record_id', 'maternal_care_records'],
    ];

    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('announcement_id')->nullable()->constrained('announcements')->nullOnDelete();
            $table->foreignId('checkup_record_id')->nullable()->constrained('checkup_records')->nullOnDelete();
            $table->foreignId('immunization_record_id')->nullable()->constrained('immunization_records')->nullOnDelete();
            $table->foreignId('maternal_care_record_id')->nullable()->constrained('maternal_care_records')->nullOnDelete();
        });

        foreach (self::SOURCE_COLUMNS as $sourceType => [$column, $tableName]) {
            DB::table('notifications')
                ->where('source_type', $sourceType)
                ->whereNotNull('source_id')
                ->whereExists(function ($query) use ($tableName) {
                    $query->selectRaw('1')
                        ->from($tableName)
                        ->whereColumn($tableName.'.id', 'notifications.source_id');
                })
                ->update([$column => DB::raw('source_id')]);
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('source');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->nullableMorphs('source');
        });

        foreach (self::SOURCE_COLUMNS as $sourceType => [$column]) {
            DB::table('notifications')
                ->whereNotNull($column)
                ->update([
                    'source_type' => $sourceType,
                    'source_id' => DB::raw($column),
                ]);
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('announcement_id');
            $table->dropConstrainedForeignId('checkup_record_id');
            $table->dropConstrainedForeignId('immunization_record_id');
            $table->dropConstrainedForeignId('maternal_care_record_id');
        });
    }
};
