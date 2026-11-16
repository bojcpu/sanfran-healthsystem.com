<?php

namespace App\Http\Controllers;

use App\Enums\ReportType;
use App\Exports\CheckupReportExport;
use App\Exports\ImmunizationReportExport;
use App\Exports\MaternalCareReportExport;
use App\Exports\PatientReportExport;
use App\Exports\SmsReportExport;
use App\Models\HealthcareReport;
use App\Services\ActivityLogService;
use App\Services\ReportGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private ReportGeneratorService $reportGenerator,
    ) {}

    public function index(): View
    {
        $reportsByType = $this->reportGenerator->ensureMonthlyReports();

        return view('reports.index', [
            'reportsByType' => $reportsByType,
            'reportTypes' => ReportType::cases(),
        ]);
    }

    public function preview(HealthcareReport $report): View
    {
        $data = $this->getReportData($report);

        return view('reports.preview', [
            'report' => $report,
            'reportHeadings' => $data['headings'],
            'reportData' => $data['rows'],
            'reportRecordCount' => count($data['rows']),
        ]);
    }

    public function pdf(HealthcareReport $report)
    {
        $data = $this->getReportData($report);

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'reportHeadings' => $data['headings'],
            'reportData' => $data['rows'],
            'reportRecordCount' => count($data['rows']),
        ]);

        return $pdf->download("sanfran-{$report->report_type->value}-report.pdf");
    }

    public function excel(HealthcareReport $report): BinaryFileResponse
    {
        return Excel::download($this->getReportExport($report), "sanfran-{$report->report_type->value}-report.xlsx");
    }

    /**
     * @return array{headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function getReportData(HealthcareReport $report): array
    {
        $export = $this->getReportExport($report);

        return [
            'headings' => $export->headings(),
            'rows' => $export->collection()
                ->map(fn ($row): array => $export->map($row))
                ->all(),
        ];
    }

    private function getReportExport(HealthcareReport $report): FromCollection&WithHeadings&WithMapping
    {
        return match ($report->report_type) {
            ReportType::Patient => new PatientReportExport($report),
            ReportType::Checkup => new CheckupReportExport($report),
            ReportType::Immunization => new ImmunizationReportExport($report),
            ReportType::MaternalCare => new MaternalCareReportExport($report),
            ReportType::Sms => new SmsReportExport($report),
        };
    }
}
