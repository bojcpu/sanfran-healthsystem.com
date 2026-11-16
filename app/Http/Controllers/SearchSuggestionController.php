<?php

namespace App\Http\Controllers;

use App\Enums\ArchiveRecordType;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\SmsNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchSuggestionController extends Controller
{
    public function __invoke(Request $request, string $source, ?string $context = null): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);
        $term = trim($validated['q'] ?? '');

        if ($term === '') {
            return response()->json(['suggestions' => []]);
        }

        $user = $request->user();

        if ($source === 'users' && ! $user->role->canViewUsers()) {
            abort(403);
        }

        if ($source === 'activity-logs' && ! $user->role->canViewActivityLogs()) {
            abort(403);
        }

        if ($source === 'sms' && ! $user->isSystemAdmin() && ! $user->isMidwife()) {
            abort(403);
        }

        $suggestions = [];

        switch ($source) {
            case 'patients':
                $this->suggestPatients($term, $suggestions);
                break;
            case 'checkups':
                $this->suggestCheckups($term, $suggestions);
                break;
            case 'immunizations':
                $this->suggestImmunizations($term, $suggestions);
                break;
            case 'maternal-care':
                $this->suggestMaternalCare($term, $suggestions);
                break;
            case 'sms':
                $this->suggestSms($term, $suggestions);
                break;
            case 'users':
                $this->suggestUsers($term, $suggestions, $user);
                break;
            case 'activity-logs':
                $this->suggestActivityLogs($term, $suggestions);
                break;
            case 'archive':
                $this->suggestArchive($term, $suggestions, $context, $user->role);
                break;
            default:
                abort(404);
        }

        return response()->json(['suggestions' => array_values($suggestions)]);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestPatients(string $term, array &$suggestions): void
    {
        $patients = Patient::query()
            ->where(function (Builder $query) use ($term): void {
                $this->wherePatientMatches($query, $term, true);
            })
            ->latest()
            ->get();

        $this->addEntries($patients, function (Patient $patient): array {
            $label = $patient->full_name;

            return [
                ['value' => $patient->first_name, 'label' => $label],
                ['value' => $patient->last_name, 'label' => $label],
            ];
        }, $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestCheckups(string $term, array &$suggestions): void
    {
        $records = CheckupRecord::query()
            ->with('patient')
            ->where(function (Builder $query) use ($term): void {
                $query->where('diagnosis', 'like', "%{$term}%")
                    ->orWhereHas('patient', fn (Builder $patientQuery) => $this->wherePatientMatches($patientQuery, $term));
            })
            ->latest('visit_date')
            ->get();

        $this->addEntries($records, function (CheckupRecord $record): array {
            return $record->patient ? $this->patientEntries($record->patient) : [];
        }, $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestImmunizations(string $term, array &$suggestions): void
    {
        $records = ImmunizationRecord::query()
            ->with('patient')
            ->where(function (Builder $query) use ($term): void {
                $query->where('vaccine_name', 'like', "%{$term}%")
                    ->orWhereHas('patient', fn (Builder $patientQuery) => $this->wherePatientMatches($patientQuery, $term));
            })
            ->latest('vaccination_date')
            ->get();

        $this->addEntries($records, fn (ImmunizationRecord $record) => $record->patient
            ? $this->patientEntries($record->patient)
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestMaternalCare(string $term, array &$suggestions): void
    {
        $records = MaternalCareRecord::query()
            ->with('patient')
            ->whereHas('patient', fn (Builder $query) => $this->wherePatientMatches($query, $term))
            ->latest('visit_date')
            ->get();

        $this->addEntries($records, fn (MaternalCareRecord $record) => $record->patient
            ? $this->patientEntries($record->patient)
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestSms(string $term, array &$suggestions): void
    {
        $records = SmsNotification::query()
            ->with('patient')
            ->where(function (Builder $query) use ($term): void {
                $query->where('mobile_number', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%")
                    ->orWhereHas('patient', function (Builder $patientQuery) use ($term): void {
                        $patientQuery->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%");
                    });
            })
            ->latest()
            ->get();

        $this->addEntries($records, fn (SmsNotification $record) => $record->patient
            ? $this->patientEntries($record->patient)
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestUsers(string $term, array &$suggestions, User $user): void
    {
        $records = User::query()
            ->when($user->isMidwife(), fn (Builder $query) => $query->where('role', \App\Enums\UserRole::Bhw))
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->latest()
            ->get();

        $this->addEntries($records, fn (User $record) => [
            ['value' => $record->name, 'label' => $record->name],
        ], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestActivityLogs(string $term, array &$suggestions): void
    {
        $records = ActivityLog::query()
            ->where(function (Builder $query) use ($term): void {
                $query->where('action', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('module', 'like', "%{$term}%")
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$term}%"));
            })
            ->latest()
            ->get();

        $this->addEntries($records, fn (ActivityLog $record) => $record->user
            ? [['value' => $record->user->name, 'label' => $record->user->name]]
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestArchive(string $term, array &$suggestions, ?string $context, \App\Enums\UserRole $role): void
    {
        $type = ArchiveRecordType::tryFrom($context ?? '');

        if (! $type || ! $type->isAllowedFor($role)) {
            abort(403);
        }

        switch ($type) {
            case ArchiveRecordType::Patients:
                $this->suggestArchivedPatients($term, $suggestions);
                break;
            case ArchiveRecordType::Checkups:
                $this->suggestArchivedCheckups($term, $suggestions);
                break;
            case ArchiveRecordType::Immunizations:
                $this->suggestArchivedImmunizations($term, $suggestions);
                break;
            case ArchiveRecordType::MaternalCare:
                $this->suggestArchivedMaternalCare($term, $suggestions);
                break;
            case ArchiveRecordType::Announcements:
                $this->suggestArchivedAnnouncements($term, $suggestions);
                break;
        }
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestArchivedPatients(string $term, array &$suggestions): void
    {
        $records = Patient::onlyTrashed()
            ->where(function (Builder $query) use ($term): void {
                $this->wherePatientMatches($query, $term, true);
            })
            ->latest('deleted_at')
            ->get();

        $this->addEntries($records, fn (Patient $patient) => $this->patientEntries($patient), $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestArchivedCheckups(string $term, array &$suggestions): void
    {
        $records = CheckupRecord::onlyTrashed()
            ->with(['patient' => fn ($query) => $query->withTrashed()])
            ->whereIn('patient_id', Patient::withTrashed()
                ->where(fn (Builder $query) => $this->wherePatientMatches($query, $term))
                ->select('id'))
            ->latest('deleted_at')
            ->get();

        $this->addEntries($records, fn (CheckupRecord $record) => $record->patient
            ? $this->patientEntries($record->patient)
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestArchivedImmunizations(string $term, array &$suggestions): void
    {
        $records = ImmunizationRecord::onlyTrashed()
            ->with(['patient' => fn ($query) => $query->withTrashed()])
            ->where(function (Builder $query) use ($term): void {
                $query->where('vaccine_name', 'like', "%{$term}%")
                    ->orWhereIn('patient_id', Patient::withTrashed()
                        ->where(fn (Builder $patientQuery) => $this->wherePatientMatches($patientQuery, $term))
                        ->select('id'));
            })
            ->latest('deleted_at')
            ->get();

        $this->addEntries($records, fn (ImmunizationRecord $record) => $record->patient
            ? $this->patientEntries($record->patient)
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestArchivedMaternalCare(string $term, array &$suggestions): void
    {
        $records = MaternalCareRecord::onlyTrashed()
            ->with(['patient' => fn ($query) => $query->withTrashed()])
            ->whereIn('patient_id', Patient::withTrashed()
                ->where(fn (Builder $query) => $this->wherePatientMatches($query, $term))
                ->select('id'))
            ->latest('deleted_at')
            ->get();

        $this->addEntries($records, fn (MaternalCareRecord $record) => $record->patient
            ? $this->patientEntries($record->patient)
            : [], $term, $suggestions);
    }

    /**
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function suggestArchivedAnnouncements(string $term, array &$suggestions): void
    {
        $records = Announcement::onlyTrashed()
            ->where('title', 'like', "%{$term}%")
            ->latest('deleted_at')
            ->get();

        $this->addEntries($records, fn (Announcement $record) => [[
            'value' => $record->title,
            'label' => $record->title,
        ]], $term, $suggestions);
    }

    private function wherePatientMatches(Builder $query, string $term, bool $includeContact = false): Builder
    {
        return $query->where(function (Builder $patientQuery) use ($term, $includeContact): void {
            $patientQuery->where('patient_code', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%");

            if ($includeContact) {
                $patientQuery->orWhere('contact_number', 'like', "%{$term}%");
            }
        });
    }

    /**
     * @return list<array{value: string|null, label: string}>
     */
    private function patientEntries(Patient $patient): array
    {
        return [
            ['value' => $patient->first_name, 'label' => $patient->full_name],
            ['value' => $patient->last_name, 'label' => $patient->full_name],
        ];
    }

    /**
     * @param Collection<int, mixed> $records
     * @param callable(mixed): array<int, array{value: string|null, label: string}> $entries
     * @param array<string, array{value: string, label: string}> $suggestions
     */
    private function addEntries(Collection $records, callable $entries, string $term, array &$suggestions): void
    {
        foreach ($records as $record) {
            foreach ($entries($record) as $entry) {
                $value = $entry['value'];

                if (! \is_string($value) || $value === '') {
                    continue;
                }

                $key = mb_strtolower($entry['label']);
                $suggestions[$key] ??= ['value' => $term, 'label' => $entry['label']];
            }
        }
    }
}
