@extends('layouts.app')

@section('title', __('tickets_index_title') . ' - ' . config('app.name'))

@section('content')
@php
    $statusKeyMap = ['Open' => 'status_open', 'In Progress' => 'status_in_progress', 'Resolved' => 'status_resolved', 'Closed' => 'status_closed'];
    $escKeyMap = ['Normal' => 'tickets_escalation_normal', 'Sedang' => 'tickets_escalation_medium', 'Tinggi' => 'tickets_escalation_high', 'KRITIS' => 'tickets_escalation_critical'];
@endphp
<div class="space-y-6 max-w-[1600px]">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-50 tracking-tight">{{ __('tickets_index_heading') }}</h1>
            <p class="text-sm text-slate-400 mt-1 max-w-xl">{{ __('tickets_index_intro') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('export.rekap') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-4 py-2.5 text-sm font-medium text-emerald-300 hover:bg-emerald-500/20 transition">📥 {{ __('excel_rekap') }}</a>
            <a href="{{ route('export.per-ticket') }}" class="inline-flex items-center gap-2 rounded-xl border border-sky-500/50 bg-sky-500/10 px-4 py-2.5 text-sm font-medium text-sky-300 hover:bg-sky-500/20 transition">📥 {{ __('tickets_excel_all') }}</a>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/30 hover:brightness-110 transition">+ {{ __('tickets_new_manual') }}</a>
        </div>
    </div>

    {{-- Petunjuk singkat --}}
    <div class="rounded-xl border border-slate-700/60 bg-slate-800/50 px-4 py-3 text-sm text-slate-300">
        <span class="font-medium text-slate-200">Cara download: </span>
        Centang tiket di kolom <strong class="text-amber-200/90">Pilih</strong> → klik <strong class="text-violet-200">Unduh Tiket Terpilih</strong>. Atau klik <strong class="text-sky-200">📥 Unduh</strong> per baris untuk satu tiket.
    </div>

    <div class="rounded-2xl bg-slate-900/60 border border-slate-700/80 shadow-xl shadow-black/20 overflow-hidden">
        <form method="GET" action="{{ route('tickets.index') }}" class="px-4 py-3 border-b border-slate-700/80 bg-slate-800/30">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Filter</span>
                <div class="flex flex-wrap items-center gap-2">
                <select name="status" class="bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400/50">
                    <option value="all" {{ ($filters['status'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('tickets_filter_status_all') }}</option>
                    <option value="Open" {{ ($filters['status'] ?? '') === 'Open' ? 'selected' : '' }}>{{ __('status_open') }}</option>
                    <option value="In Progress" {{ ($filters['status'] ?? '') === 'In Progress' ? 'selected' : '' }}>{{ __('status_in_progress') }}</option>
                    <option value="Resolved" {{ ($filters['status'] ?? '') === 'Resolved' ? 'selected' : '' }}>{{ __('status_resolved') }}</option>
                    <option value="Closed" {{ ($filters['status'] ?? '') === 'Closed' ? 'selected' : '' }}>{{ __('status_closed') }}</option>
                </select>
                <select name="urgency" class="bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400/50">
                    <option value="all" {{ ($filters['urgency'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('tickets_filter_escalation_all') }}</option>
                    <option value="Normal" {{ ($filters['urgency'] ?? '') === 'Normal' ? 'selected' : '' }}>{{ __('tickets_escalation_normal') }}</option>
                    <option value="Sedang" {{ ($filters['urgency'] ?? '') === 'Sedang' ? 'selected' : '' }}>{{ __('tickets_escalation_medium') }}</option>
                    <option value="Tinggi" {{ ($filters['urgency'] ?? '') === 'Tinggi' ? 'selected' : '' }}>{{ __('tickets_escalation_high') }}</option>
                    <option value="KRITIS" {{ ($filters['urgency'] ?? '') === 'KRITIS' ? 'selected' : '' }}>{{ __('tickets_escalation_critical') }}</option>
                </select>
                <select name="level" class="bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400/50">
                    <option value="all" {{ ($filters['level'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('tickets_filter_level_all') }}</option>
                    <option value="Mikro" {{ ($filters['level'] ?? '') === 'Mikro' ? 'selected' : '' }}>Mikro</option>
                    <option value="Meso" {{ ($filters['level'] ?? '') === 'Meso' ? 'selected' : '' }}>Meso</option>
                    <option value="Makro" {{ ($filters['level'] ?? '') === 'Makro' ? 'selected' : '' }}>Makro</option>
                </select>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('tickets_search_placeholder') }}"
                    class="min-w-[180px] bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/50" />
                <button type="submit" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-200 hover:bg-slate-700 transition">{{ __('tickets_filter_btn') }}</button>
                </div>
            </div>
        </form>

        <form id="form-export-selected" method="POST" action="{{ route('export.per-ticket.post') }}">
            @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-800/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/80">
                        <th class="px-4 py-3 w-12 text-center">
                            <input type="checkbox" id="select-all-tickets" class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-400/70" title="Pilih semua">
                        </th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_ticket') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_status') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_level') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_category') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_score') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_source') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('tickets_th_recorded') }}</th>
                        <th class="px-4 py-3 text-right font-medium">{{ __('tickets_th_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($tickets as $t)
                        <tr class="bg-slate-900/40 hover:bg-slate-800/60 transition-colors">
                            <td class="px-4 py-3 align-middle text-center">
                                <input type="checkbox" name="ids[]" value="{{ $t->id }}" class="ticket-checkbox rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-400/70" title="Pilih untuk download">
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <a href="{{ route('tickets.show', $t->id) }}" class="text-xs font-medium text-sky-300 hover:text-sky-200">{{ $t->ticket_id }}</a>
                                <div class="text-xs text-slate-300 mt-0.5 line-clamp-2">{{ $t->judul_sinyal }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $t->nama_lokasi ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px]
                                    @if($t->status_ticket === 'Open') bg-sky-500/20 text-sky-300 border-sky-500/40
                                    @elseif($t->status_ticket === 'In Progress') bg-amber-500/20 text-amber-300 border-amber-500/40
                                    @elseif($t->status_ticket === 'Resolved') bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                                    @else bg-slate-500/20 text-slate-300 border-slate-500/40
                                    @endif">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span> {{ isset($statusKeyMap[$t->status_ticket]) ? __($statusKeyMap[$t->status_ticket]) : $t->status_ticket }}
                                </span>
                                <div class="mt-1">
                                    @php $escKey = $escKeyMap[$t->status_eskalasi ?? 'Normal'] ?? null; @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] bg-slate-700/30 text-slate-200 border-slate-500/40">{{ $escKey ? __($escKey) : ($t->status_eskalasi ?? 'Normal') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-slate-200">{{ $t->level_sinyal ?? '—' }}</td>
                            <td class="px-4 py-3 align-middle text-slate-200">{{ $t->kategori_sinyal ?? '—' }}</td>
                            <td class="px-4 py-3 align-middle">
                                <span class="font-semibold text-slate-100">{{ $t->skor_urgensi_hukum ?? '—' }}</span>
                                <div class="text-[11px] text-slate-500">max {{ $t->skor_urgensi_tertinggi ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle text-slate-200">
                                <span class="uppercase text-[10px]">{{ $t->jenis_sumber ?? '-' }}</span>
                                @if($t->nama_pelapor)<div class="text-[10px] text-slate-500">{{ $t->nama_pelapor }}</div>@endif
                            </td>
                            <td class="px-4 py-3 align-middle text-slate-300 text-xs whitespace-nowrap">{{ $t->waktu_catat ? $t->waktu_catat->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-4 py-3 align-middle text-right">
                                <a href="{{ route('export.per-ticket', ['ticket_id' => $t->ticket_id]) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-600 bg-slate-800/80 px-2.5 py-1.5 text-xs font-medium text-sky-300 hover:bg-sky-500/20 hover:border-sky-500/40 transition mr-1.5">📥 Unduh</a>
                                <a href="{{ route('tickets.show', $t->id) }}" class="inline-flex items-center rounded-lg border border-slate-600 bg-slate-800/80 px-2.5 py-1.5 text-xs font-medium text-slate-200 hover:bg-amber-500/20 hover:border-amber-500/40 transition">{{ __('tickets_edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-3 py-4 text-center text-slate-500">{{ __('tickets_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="selected-bar" class="mx-4 mb-4 hidden flex flex-wrap items-center gap-3 rounded-xl border border-violet-500/40 bg-violet-500/10 px-4 py-3">
            <span id="selected-count" class="text-sm font-medium text-violet-200">0 tiket dipilih.</span>
            <button type="submit" name="export_selected" class="inline-flex items-center gap-2 rounded-lg bg-violet-500 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-600 transition">📥 Unduh Tiket Terpilih</button>
            <button type="button" id="clear-selection" class="text-xs text-slate-400 hover:text-slate-200 underline">Batalkan pilihan</button>
        </div>
        </form>

        <script>
            (function() {
                var form = document.getElementById('form-export-selected');
                var selectAll = document.getElementById('select-all-tickets');
                var checkboxes = form ? form.querySelectorAll('.ticket-checkbox') : [];
                var bar = document.getElementById('selected-bar');
                var countEl = document.getElementById('selected-count');
                var clearBtn = document.getElementById('clear-selection');

                function updateBar() {
                    var n = 0;
                    for (var i = 0; i < checkboxes.length; i++) if (checkboxes[i].checked) n++;
                    if (countEl) countEl.textContent = n + ' tiket dipilih untuk di-download.';
                    if (bar) bar.classList.toggle('hidden', n === 0);
                    if (selectAll) selectAll.checked = n > 0 && n === checkboxes.length;
                }
                function toggleAll() {
                    var checked = selectAll && selectAll.checked;
                    for (var i = 0; i < checkboxes.length; i++) checkboxes[i].checked = checked;
                    updateBar();
                }
                if (selectAll) selectAll.addEventListener('change', toggleAll);
                for (var i = 0; i < checkboxes.length; i++) checkboxes[i].addEventListener('change', updateBar);
                if (clearBtn) clearBtn.addEventListener('click', function() {
                    if (selectAll) selectAll.checked = false;
                    for (var j = 0; j < checkboxes.length; j++) checkboxes[j].checked = false;
                    updateBar();
                });
                if (form) form.addEventListener('submit', function(e) {
                    var n = 0;
                    for (var k = 0; k < checkboxes.length; k++) if (checkboxes[k].checked) n++;
                    if (n === 0) {
                        e.preventDefault();
                        alert('Pilih minimal satu tiket (centang di kolom Pilih), lalu klik Unduh Tiket Terpilih.');
                    }
                });
            })();
        </script>

        @if($tickets->hasPages())
            <div class="px-4 py-3 border-t border-slate-700/80 flex items-center justify-between gap-4 text-sm text-slate-400 bg-slate-800/30">
                <span>Halaman <span class="font-medium text-slate-200">{{ $tickets->currentPage() }}</span> dari {{ $tickets->lastPage() }} <span class="text-slate-500">({{ $tickets->total() }} tiket)</span></span>
                <div class="flex items-center gap-2">
                    @if($tickets->onFirstPage())
                        <span class="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-500 opacity-40">{{ __('tickets_prev') }}</span>
                    @else
                        <a href="{{ $tickets->previousPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-200 hover:bg-slate-700 transition">{{ __('tickets_prev') }}</a>
                    @endif
                    @if($tickets->hasMorePages())
                        <a href="{{ $tickets->nextPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-200 hover:bg-slate-700 transition">{{ __('tickets_next') }}</a>
                    @else
                        <span class="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-500 opacity-40">{{ __('tickets_next') }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
