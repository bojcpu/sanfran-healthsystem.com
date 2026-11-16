<?php

namespace App\Http\Controllers;

use App\Enums\ArchiveRecordType;
use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $role = $this->authenticatedUser()->role;
        $types = ArchiveRecordType::visibleFor($role);
        $type = ArchiveRecordType::tryFrom((string) $request->query('type', '')) ?? $types[0];

        if (! $type->isAllowedFor($role)) {
            $type = $types[0];
        }

        $search = trim((string) $request->query('search', ''));

        $records = $this->archivedQuery($type, $search)
            ->paginate(15)
            ->withQueryString();

        return view('archive.index', compact('type', 'types', 'records', 'search'));
    }

    public function restore(string $type, int $id): RedirectResponse
    {
        $recordType = ArchiveRecordType::tryFrom($type);

        if (! $recordType || ! $recordType->canRestoreFor($this->authenticatedUser()->role)) {
            abort(403);
        }

        $record = $recordType->modelClass()::query()
            ->onlyTrashed()
            ->findOrFail($id);

        $record->restore();

        $this->activityLog->log(
            'restore',
            $this->restoreDescription($recordType, $record),
            $recordType->module(),
        );

        return redirect()
            ->route('archive.index', ['type' => $recordType->value])
            ->with('success', 'Record restored successfully.');
    }

    /**
     * @return Builder<Patient>|Builder<CheckupRecord>|Builder<ImmunizationRecord>|Builder<MaternalCareRecord>|Builder<Announcement>
     */
    private function archivedQuery(ArchiveRecordType $type, string $search): Builder
    {
        $patientSearch = function (Builder $query) use ($search): void {
            $query->withTrashed()
                ->where(function (Builder $patientQuery) use ($search): void {
                    $patientQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                });
        };

        return match ($type) {
            ArchiveRecordType::Patients => Patient::query()
                ->onlyTrashed()
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $inner) use ($search): void {
                        $inner->where('patient_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%");
                    });
                })
                ->latest('deleted_at'),
            ArchiveRecordType::Checkups => CheckupRecord::query()
                ->onlyTrashed()
                ->with(['patient' => fn ($query) => $query->withTrashed(), 'user'])
                ->when($search !== '', fn (Builder $query) => $query->whereHas('patient', $patientSearch))
                ->latest('deleted_at'),
            ArchiveRecordType::Immunizations => ImmunizationRecord::query()
                ->onlyTrashed()
                ->with(['patient' => fn ($query) => $query->withTrashed(), 'user'])
                ->when($search !== '', function (Builder $query) use ($search, $patientSearch): void {
                    $query->where(function (Builder $inner) use ($search, $patientSearch): void {
                        $inner->where('vaccine_name', 'like', "%{$search}%")
                            ->orWhereHas('patient', $patientSearch);
                    });
                })
                ->latest('deleted_at'),
            ArchiveRecordType::MaternalCare => MaternalCareRecord::query()
                ->onlyTrashed()
                ->with(['patient' => fn ($query) => $query->withTrashed(), 'user'])
                ->when($search !== '', fn (Builder $query) => $query->whereHas('patient', $patientSearch))
                ->latest('deleted_at'),
            ArchiveRecordType::Announcements => Announcement::query()
                ->onlyTrashed()
                ->with('postedBy')
                ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', "%{$search}%"))
                ->latest('deleted_at'),
        };
    }

    private function restoreDescription(ArchiveRecordType $type, Patient|CheckupRecord|ImmunizationRecord|MaternalCareRecord|Announcement $record): string
    {
        return match ($type) {
            ArchiveRecordType::Patients => "Restored patient {$record->patient_code}: {$record->full_name}.",
            ArchiveRecordType::Checkups => "Restored checkup record #{$record->id}.",
            ArchiveRecordType::Immunizations => "Restored immunization record #{$record->id}.",
            ArchiveRecordType::MaternalCare => "Restored maternal care record #{$record->id}.",
            ArchiveRecordType::Announcements => "Restored announcement: {$record->title}.",
        };
    }

    private function authenticatedUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
