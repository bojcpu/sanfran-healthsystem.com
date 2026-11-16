@extends('layouts.app')

@section('content')
    <x-page-header
        title="Dashboard"
        description="Overview of barangay health services and activities."
    />

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card title="Total Patients" :value="number_format($stats['patients'] ?? 0)" color="primary" style="--reveal-delay: 0ms">
            <x-slot:iconSlot>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </x-slot:iconSlot>
        </x-stat-card>
        <x-stat-card title="Checkups This Month" :value="number_format($stats['checkups'] ?? 0)" color="blue" style="--reveal-delay: 80ms">
            <x-slot:iconSlot>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
            </x-slot:iconSlot>
        </x-stat-card>
        <x-stat-card title="Immunizations" :value="number_format($stats['immunizations'] ?? 0)" color="green" style="--reveal-delay: 160ms">
            <x-slot:iconSlot>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                </svg>
            </x-slot:iconSlot>
        </x-stat-card>
        <x-stat-card title="Maternal Care Visits" :value="number_format($stats['maternal_care'] ?? 0)" color="rose" style="--reveal-delay: 240ms">
            <x-slot:iconSlot>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </x-slot:iconSlot>
        </x-stat-card>
    </div>

    @php
        $registrationTotal = array_sum($charts['registrations']['data'] ?? []);
        $checkupTotal = array_sum($charts['checkups']['data'] ?? []);
        $immunizationTotal = array_sum($charts['immunizations']['data'] ?? []);
        $maternalTotal = array_sum($charts['maternal']['data'] ?? []);
        $immunizationLegend = [
            ['label' => 'Completed', 'value' => $charts['immunizations']['data'][0] ?? 0, 'swatch' => 'bg-[#094F29]'],
            ['label' => 'Scheduled', 'value' => $charts['immunizations']['data'][1] ?? 0, 'swatch' => 'bg-[#3d5c42]'],
            ['label' => 'Overdue', 'value' => $charts['immunizations']['data'][2] ?? 0, 'swatch' => 'bg-[#c9a227]'],
        ];
    @endphp

    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card chart-card reveal-on-scroll reveal-up" style="--reveal-delay: 0ms">
            <div class="chart-card-header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Patient Registrations</h3>
                    <p class="mt-0.5 text-xs text-gray-500">New records over the last 6 months.</p>
                </div>
                <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary">{{ number_format($registrationTotal) }} total</span>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="registrationsChart"></canvas>
            </div>
        </div>
        <div class="card chart-card reveal-on-scroll reveal-up" style="--reveal-delay: 100ms">
            <div class="chart-card-header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Monthly Checkups</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Consultations recorded each month.</p>
                </div>
                <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary">{{ number_format($checkupTotal) }} visits</span>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="checkupsChart"></canvas>
            </div>
        </div>
        <div class="card chart-card reveal-on-scroll reveal-up" style="--reveal-delay: 200ms">
            <div class="chart-card-header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Immunization Coverage</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Completed, scheduled, and overdue doses.</p>
                </div>
                <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary">{{ number_format($immunizationTotal) }} records</span>
            </div>
            <div class="grid items-center gap-2 sm:grid-cols-[minmax(0,1fr)_11.5rem] sm:pr-4">
                <div class="chart-canvas-wrap !h-64">
                    <canvas id="immunizationsChart"></canvas>
                </div>
                <ul class="chart-legend">
                    @foreach ($immunizationLegend as $item)
                        <li class="chart-legend-item">
                            <span class="flex items-center gap-2 text-xs font-medium text-gray-700">
                                <span class="{{ $item['swatch'] }} h-2.5 w-2.5 rounded-full ring-2 ring-white shadow-sm"></span>
                                {{ $item['label'] }}
                            </span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($item['value']) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="card chart-card reveal-on-scroll reveal-up" style="--reveal-delay: 300ms">
            <div class="chart-card-header">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Maternal Care Visits</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Prenatal visits over the last 6 months.</p>
                </div>
                <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">{{ number_format($maternalTotal) }} visits</span>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="maternalChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <div class="card card-interactive reveal-on-scroll reveal-up">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold text-gray-900">Recent Announcements</h3>
                <a href="{{ route('announcements.index') }}" class="btn-secondary text-xs">Show All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recentAnnouncements ?? [] as $announcement)
                    @php /** @var \App\Models\Announcement $announcement */ @endphp
                    <div class="px-5 py-4">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <span class="{{ $announcement->type->badgeClass() }}">{{ $announcement->type->label() }}</span>
                            <span class="text-xs text-gray-400">{{ $announcement->publish_date->format('M d, Y') }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">{{ $announcement->title }}</p>
                        <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $announcement->content }}</p>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">No announcements yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartData = @json($charts);
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const fontFamily = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";

            Chart.defaults.font.family = fontFamily;
            Chart.defaults.font.size = 18;
            Chart.defaults.color = '#6b7280';
            Chart.defaults.animation.duration = reduceMotion ? 0 : 1100;
            Chart.defaults.animation.easing = 'easeOutQuart';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(2, 27, 14, 0.94)';
            Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
            Chart.defaults.plugins.tooltip.bodyColor = '#dceee2';
            Chart.defaults.plugins.tooltip.padding = 14;
            Chart.defaults.plugins.tooltip.cornerRadius = 14;
            Chart.defaults.plugins.tooltip.displayColors = true;
            Chart.defaults.plugins.tooltip.boxPadding = 6;
            Chart.defaults.plugins.tooltip.caretPadding = 10;
            Chart.defaults.plugins.tooltip.borderColor = 'rgba(255, 255, 255, 0.12)';
            Chart.defaults.plugins.tooltip.borderWidth = 1;

            const colors = {
                forest: '#094F29',
                forestDeep: '#042a17',
                forestSoft: '#2f8c55',
                gold: '#c9a227',
                goldSoft: '#e4c86a',
                sage: '#3d5c42',
                rose: '#9f3d52',
                roseSoft: '#d48a96',
                grid: 'rgba(9, 79, 41, 0.08)',
            };

            const barShades = ['#0f6638', '#094F29', '#2f8c55', '#073f21', '#57aa75', '#06351d'];

            const makeGradient = (chart, stops, vertical = true) => {
                const { ctx, chartArea } = chart;

                if (! chartArea) {
                    return stops[0][1];
                }

                const gradient = vertical
                    ? ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
                    : ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);

                stops.forEach(([offset, color]) => gradient.addColorStop(offset, color));

                return gradient;
            };

            const axisOptions = {
                grid: {
                    color: colors.grid,
                    drawTicks: false,
                    lineWidth: 1,
                },
                border: { display: false },
                ticks: {
                    padding: 10,
                    font: { size: 18, weight: '600', family: fontFamily },
                    color: '#9ca3af',
                },
            };

            const countTooltip = (label) => ({
                callbacks: {
                    label: (context) => {
                        const value = context.parsed.y ?? context.parsed.x ?? context.parsed;

                        return ` ${label}: ${value}`;
                    },
                },
            });

            const glowLine = {
                id: 'glowLine',
                beforeDatasetsDraw(chart) {
                    if (chart.getDatasetMeta(0).type !== 'line') {
                        return;
                    }

                    chart.ctx.save();
                    chart.ctx.shadowColor = 'rgba(9, 79, 41, 0.35)';
                    chart.ctx.shadowBlur = 18;
                    chart.ctx.shadowOffsetY = 10;
                },
                afterDatasetsDraw(chart) {
                    chart.ctx.restore();
                },
            };

            const crosshair = {
                id: 'crosshair',
                afterDraw(chart) {
                    const tooltip = chart.tooltip;

                    if (! tooltip?.getActiveElements()?.length) {
                        return;
                    }

                    const { ctx, chartArea } = chart;
                    const x = tooltip.getActiveElements()[0].element.x;

                    ctx.save();
                    ctx.beginPath();
                    ctx.setLineDash([5, 5]);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = 'rgba(9, 79, 41, 0.28)';
                    ctx.moveTo(x, chartArea.top);
                    ctx.lineTo(x, chartArea.bottom);
                    ctx.stroke();
                    ctx.restore();
                },
            };

            const valueLabels = {
                id: 'valueLabels',
                afterDatasetsDraw(chart, _args, pluginOptions) {
                    const { ctx } = chart;
                    const skipTypes = pluginOptions?.skipTypes ?? [];

                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = chart.getDatasetMeta(datasetIndex);

                        if (meta.hidden || skipTypes.includes(meta.type) || dataset.hideLabels) {
                            return;
                        }

                        meta.data.forEach((element, index) => {
                            const value = Number(dataset.data[index] ?? 0);

                            if (value <= 0) {
                                return;
                            }

                            const position = element.tooltipPosition();

                            ctx.save();
                            ctx.font = `700 18px ${fontFamily}`;
                            ctx.fillStyle = '#4b5563';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            if (chart.options.indexAxis === 'y') {
                                ctx.textAlign = 'left';
                                ctx.fillText(value.toLocaleString(), position.x + 10, position.y);
                            } else {
                                ctx.fillText(value.toLocaleString(), position.x, position.y - 14);
                            }

                            ctx.restore();
                        });
                    });
                },
            };

            const doughnutCenter = {
                id: 'doughnutCenter',
                afterDraw(chart) {
                    const total = chart.data.datasets[0].data.reduce((sum, value) => sum + Number(value), 0);
                    const { ctx, chartArea } = chart;
                    const x = (chartArea.left + chartArea.right) / 2;
                    const y = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#111827';
                    ctx.font = `700 18px ${fontFamily}`;
                    ctx.fillText(total.toLocaleString(), x, y - 8);
                    ctx.fillStyle = '#6b7280';
                    ctx.font = `500 18px ${fontFamily}`;
                    ctx.fillText('Total records.', x, y + 14);
                    ctx.restore();
                },
            };

            new Chart(document.getElementById('registrationsChart'), {
                type: 'line',
                data: {
                    labels: chartData.registrations.labels,
                    datasets: [{
                        label: 'Registrations',
                        data: chartData.registrations.data,
                        borderColor: colors.forest,
                        backgroundColor: (context) => makeGradient(context.chart, [
                            [0, 'rgba(47, 140, 85, 0.38)'],
                            [0.55, 'rgba(9, 79, 41, 0.16)'],
                            [1, 'rgba(9, 79, 41, 0.01)'],
                        ]),
                        fill: true,
                        tension: 0.42,
                        borderWidth: 3.5,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: colors.forest,
                        pointBorderWidth: 3,
                        pointHoverBackgroundColor: colors.gold,
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    layout: { padding: { top: 18, right: 8 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: countTooltip('Registrations'),
                    },
                    scales: {
                        x: axisOptions,
                        y: {
                            ...axisOptions,
                            beginAtZero: true,
                            grace: '18%',
                            ticks: { ...axisOptions.ticks, precision: 0 },
                        },
                    },
                },
                plugins: [glowLine, crosshair, valueLabels],
            });

            new Chart(document.getElementById('checkupsChart'), {
                data: {
                    labels: chartData.checkups.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Checkups',
                            data: chartData.checkups.data,
                            backgroundColor: (context) => {
                                if (! context.chart.chartArea) {
                                    return barShades[context.dataIndex] ?? colors.forest;
                                }

                                const { ctx, chartArea } = context.chart;
                                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                gradient.addColorStop(0, barShades[context.dataIndex] ?? colors.forestSoft);
                                gradient.addColorStop(1, colors.forestDeep);

                                return gradient;
                            },
                            hoverBackgroundColor: colors.forest,
                            borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 5, bottomRight: 5 },
                            borderSkipped: false,
                            maxBarThickness: 44,
                            order: 2,
                        },
                        {
                            type: 'line',
                            label: 'Trend',
                            data: chartData.checkups.data,
                            borderColor: colors.gold,
                            backgroundColor: colors.goldSoft,
                            borderWidth: 2.5,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: colors.gold,
                            pointBorderWidth: 2,
                            hideLabels: true,
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    layout: { padding: { top: 18 } },
                    animation: reduceMotion ? false : {
                        delay: (context) => (context.type === 'data' ? context.dataIndex * 80 : 0),
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...countTooltip('Checkups'),
                            filter: (item) => item.dataset.type !== 'line',
                        },
                    },
                    scales: {
                        x: axisOptions,
                        y: {
                            ...axisOptions,
                            beginAtZero: true,
                            grace: '18%',
                            ticks: { ...axisOptions.ticks, precision: 0 },
                        },
                    },
                },
                plugins: [valueLabels],
            });

            new Chart(document.getElementById('immunizationsChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.immunizations.labels,
                    datasets: [{
                        data: chartData.immunizations.data,
                        backgroundColor: [colors.forest, colors.sage, colors.gold],
                        hoverBackgroundColor: ['#073f21', '#334d37', '#a8861c'],
                        borderColor: '#ffffff',
                        borderWidth: 5,
                        hoverOffset: 12,
                        spacing: 4,
                        borderRadius: 10,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ` ${context.label}: ${context.parsed}`,
                            },
                        },
                    },
                },
                plugins: [doughnutCenter],
            });

            new Chart(document.getElementById('maternalChart'), {
                type: 'bar',
                data: {
                    labels: chartData.maternal.labels,
                    datasets: [{
                        label: 'Visits',
                        data: chartData.maternal.data,
                        backgroundColor: (context) => {
                            if (! context.chart.chartArea) {
                                return colors.rose;
                            }

                            const { ctx, chartArea } = context.chart;
                            const gradient = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                            gradient.addColorStop(0, colors.rose);
                            gradient.addColorStop(1, colors.roseSoft);

                            return gradient;
                        },
                        hoverBackgroundColor: colors.rose,
                        borderRadius: 999,
                        borderSkipped: false,
                        maxBarThickness: 22,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    layout: { padding: { right: 28 } },
                    animation: reduceMotion ? false : {
                        delay: (context) => (context.type === 'data' ? context.dataIndex * 80 : 0),
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ` Visits: ${context.parsed.x}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            ...axisOptions,
                            beginAtZero: true,
                            grace: '18%',
                            ticks: { ...axisOptions.ticks, precision: 0 },
                        },
                        y: {
                            ...axisOptions,
                            grid: { display: false },
                            ticks: { ...axisOptions.ticks, color: '#4b5563' },
                        },
                    },
                },
                plugins: [valueLabels],
            });
        });
    </script>
@endpush
