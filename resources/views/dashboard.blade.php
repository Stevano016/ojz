@extends('layouts.app')

@section('title', __('nav_dashboard') . ' - ' . config('app.name'))

@section('content')
<div class="space-y-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-semibold text-slate-50">{{ __('dashboard_title') }}</h1>
            <p class="text-sm text-slate-400 mt-1 max-w-xl">{{ __('dashboard_subtitle') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('export.rekap') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/50 bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-300 hover:bg-emerald-500/20">📥 {{ __('excel_rekap') }}</a>
            <a href="{{ route('export.per-ticket') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-sky-500/50 bg-sky-500/10 px-3 py-2 text-xs font-medium text-sky-300 hover:bg-sky-500/20">📥 {{ __('excel_per_ticket') }}</a>
        </div>
    </div>

    @php
        $s = $stats['summary'] ?? [];
        $byLevel = $stats['by_level_laporan'] ?? [];
        $mikro = $byLevel['Mikro'] ?? 0;
        $meso = $byLevel['Meso'] ?? 0;
        $makro = $byLevel['Makro'] ?? 0;
        $levelOrder = ['Mikro', 'Meso', 'Makro'];
        $levelLaporanDataAll = collect($levelOrder)->map(fn ($name) => ['name' => $name, 'value' => $byLevel[$name] ?? 0])->values()->all();
        $ticketsByDate = collect($stats['tickets_by_date'] ?? [])->reverse()->values()->all();
        $statusData = [
            ['name' => __('status_open'), 'value' => $s['open'] ?? 0],
            ['name' => __('status_in_progress'), 'value' => $s['in_progress'] ?? 0],
            ['name' => __('status_resolved'), 'value' => $s['resolved'] ?? 0],
            ['name' => __('status_closed'), 'value' => $s['closed'] ?? 0],
        ];
        $scoreDistribution = $stats['score_distribution'] ?? [];
    @endphp

    {{-- Level Sinyal — pokok dashboard (sama dengan React) --}}
    <div class="rounded-2xl border-2 border-amber-500/40 bg-gradient-to-br from-amber-950/20 via-slate-900/90 to-slate-900/90 p-4 md:p-5 shadow-xl shadow-amber-500/10">
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="inline-flex items-center rounded-lg bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-300 border border-amber-500/40">{{ __('focus_main') }}</span>
            <h2 class="text-base md:text-lg font-semibold text-slate-100">{{ __('level_signal') }}</h2>
            <span class="text-[11px] text-slate-500">{{ __('level_signal_hint') }}</span>
        </div>
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400">Mikro</div>
                    <div class="mt-1 font-semibold text-2xl md:text-3xl text-slate-50">{{ $mikro }}</div>
                </div>
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
            </div>
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400">Meso</div>
                    <div class="mt-1 font-semibold text-2xl md:text-3xl text-slate-50">{{ $meso }}</div>
                </div>
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
            </div>
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400">Makro</div>
                    <div class="mt-1 font-semibold text-2xl md:text-3xl text-slate-50">{{ $makro }}</div>
                </div>
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
            </div>
        </div>
        <div class="h-44 md:h-52">
            <canvas id="chartLevelSinyal" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between shadow-lg shadow-black/30">
            <div>
                <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('total_tickets') }}</div>
                <div class="mt-1 font-semibold text-xl text-slate-50">{{ $s['total'] ?? 0 }}</div>
            </div>
            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-emerald-400/90 to-emerald-500 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
        </div>
        <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between shadow-lg shadow-black/30">
            <div>
                <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('open') }}</div>
                <div class="mt-1 font-semibold text-xl text-slate-50">{{ $s['open'] ?? 0 }}</div>
            </div>
            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-sky-400/90 to-sky-500 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
        </div>
        <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between shadow-lg shadow-black/30">
            <div>
                <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('critical_high') }}</div>
                <div class="mt-1 font-semibold text-xl text-slate-50">{{ ($stats['by_urgency']['KRITIS'] ?? 0) + ($stats['by_urgency']['Tinggi'] ?? 0) }}</div>
            </div>
            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-rose-400/90 to-rose-500 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
        </div>
        <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between shadow-lg shadow-black/30">
            <div>
                <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('avg_urgency_score') }}</div>
                <div class="mt-1 font-semibold text-xl text-slate-50">{{ $s['avg_score'] ?? 0 }}</div>
            </div>
            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-amber-400/90 to-amber-500 flex items-center justify-center"><span class="h-2 w-2 rounded-full bg-slate-950/80"></span></div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        {{-- Volume tiket per hari (30 hari terakhir) --}}
        <div class="md:col-span-2 rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
            <div class="mb-3">
                <div class="text-xs font-medium text-slate-300">{{ __('volume_per_day') }}</div>
                <div class="text-[11px] text-slate-500">{{ __('volume_per_day_hint') }}</div>
            </div>
            <div class="h-56">
                <canvas id="chartVolumePerHari" height="220"></canvas>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Komposisi status tiket (Pie) --}}
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-3 shadow-xl shadow-black/30">
                <div class="text-xs font-medium text-slate-300 mb-2">{{ __('status_composition') }}</div>
                <div class="h-40">
                    <canvas id="chartStatusPie" height="160"></canvas>
                </div>
            </div>
            {{-- Ringkasan level sinyal --}}
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-3 shadow-xl shadow-black/30">
                <div class="text-xs font-medium text-slate-300 mb-2">{{ __('level_summary') }}</div>
                <div class="flex flex-col gap-2">
                    @foreach($levelOrder as $name)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-300">{{ $name }}</span>
                            <span class="font-semibold text-slate-50">{{ $byLevel[$name] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Distribusi skor urgensi hukum --}}
    <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
        <div class="mb-3">
            <div class="text-xs font-medium text-slate-300">{{ __('score_distribution') }}</div>
            <div class="text-[11px] text-slate-500">{{ __('score_distribution_hint') }}</div>
        </div>
        <div class="h-48">
            <canvas id="chartScoreDist" height="190"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const stats = @json($stats);
    const levelLaporanDataAll = @json($levelLaporanDataAll);
    const ticketsByDate = @json($ticketsByDate);
    const statusData = @json($statusData);
    const scoreDistribution = @json($scoreDistribution);

    const gridColor = 'rgba(51, 65, 85, 0.8)';
    const textColor = '#94a3b8';
    const tooltipBg = '#0f172a';

    // 1. Level Sinyal — Bar chart (Mikro, Meso, Makro)
    if (document.getElementById('chartLevelSinyal')) {
        new Chart(document.getElementById('chartLevelSinyal'), {
            type: 'bar',
            data: {
                labels: levelLaporanDataAll.map(d => d.name),
                datasets: [{ label: 'Tiket', data: levelLaporanDataAll.map(d => d.value), backgroundColor: '#f59e0b', borderRadius: 8 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#e2e8f0', font: { size: 12, weight: 500 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } }
                }
            }
        });
    }

    // 2. Volume tiket per hari — Area/Line
    if (document.getElementById('chartVolumePerHari') && ticketsByDate.length) {
        new Chart(document.getElementById('chartVolumePerHari'), {
            type: 'line',
            data: {
                labels: ticketsByDate.map(d => d.date),
                datasets: [{
                    label: 'Total',
                    data: ticketsByDate.map(d => d.total),
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.3)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, maxRotation: 45 } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } }
                }
            }
        });
    }

    // 3. Komposisi status tiket — Pie (hanya yang value > 0)
    if (document.getElementById('chartStatusPie')) {
        const filtered = statusData.filter(d => d.value > 0);
        const colors = ['#0ea5e9', '#f59e0b', '#10b981', '#64748b'];
        if (filtered.length) {
            new Chart(document.getElementById('chartStatusPie'), {
            type: 'doughnut',
            data: {
                labels: filtered.map(d => d.name),
                datasets: [{ data: filtered.map(d => d.value), backgroundColor: colors.slice(0, filtered.length), borderWidth: 0 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 10 } } } },
                cutout: '55%'
            }
        });
        }
    }

    // 4. Distribusi skor urgensi hukum — Bar
    if (document.getElementById('chartScoreDist') && scoreDistribution.length) {
        const labels = scoreDistribution.map(d => d.range_label);
        const totals = scoreDistribution.map(d => d.total);
        new Chart(document.getElementById('chartScoreDist'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: 'Tickets', data: totals, backgroundColor: '#f97316', borderRadius: 4 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, maxRotation: 25, font: { size: 10 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } }
                }
            }
        });
    }
})();
</script>
@endsection
