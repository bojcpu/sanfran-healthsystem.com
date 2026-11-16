@extends('layouts.app')

@section('content')
    <x-page-header title="Reports" description="System-generated healthcare reports by type.">
        <x-slot:actions>
            <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary">Auto-generated monthly</span>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-8" x-data="{ selectedReport: { pdfUrl: '', excelUrl: '' } }">
        @foreach ($reportTypes as $type)
            @php
                $reports = $reportsByType->get($type->value, collect());
            @endphp
            <div class="card overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $type->label() }}</h3>
                    <p class="mt-1 text-xs text-gray-500">Automatically generated reports for this module.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="table-header table-cell">Period</th>
                                <th class="table-header table-cell">Date Range</th>
                                <th class="table-header table-cell">Totals</th>
                                <th class="table-header table-cell">Generated</th>
                                <th class="table-header table-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($reports as $report)
                                <tr>
                                    <td class="table-cell font-medium">{{ $report->report_period }}</td>
                                    <td class="table-cell text-xs">{{ $report->date_from->format('M d, Y') }} — {{ $report->date_to->format('M d, Y') }}</td>
                                    <td class="table-cell text-xs">
                                        @if ($report->total_patients) P: {{ $report->total_patients }} @endif
                                        @if ($report->total_consultations) C: {{ $report->total_consultations }} @endif
                                        @if ($report->total_immunizations) I: {{ $report->total_immunizations }} @endif
                                    </td>
                                    <td class="table-cell text-xs">{{ $report->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="table-cell">
                                        <div class="table-actions">
                                            <a href="{{ route('reports.preview', $report) }}" target="_blank" class="btn-secondary text-xs">Print</a>
                                            <button
                                                type="button"
                                                class="btn-primary text-xs"
                                                @click="selectedReport = { pdfUrl: @js(route('reports.pdf', $report)), excelUrl: @js(route('reports.excel', $report)) }; $store.modal.open('report-export')"
                                            >Export</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No reports generated yet for this type.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <x-modal name="report-export" title="Export Report" description="Choose a format to download." maxWidth="sm">
            <div class="flex flex-col gap-3 sm:flex-row">
                <a class="btn-primary flex-1" :href="selectedReport.pdfUrl" @click="$store.modal.close()">PDF</a>
                <a class="btn-secondary flex-1" :href="selectedReport.excelUrl" @click="$store.modal.close()">Excel (.xlsx)</a>
            </div>
        </x-modal>
    </div>
@endsection
