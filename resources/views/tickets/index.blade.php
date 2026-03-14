@extends('layouts.app')

@section('title', __('tickets_index_title') . ' - ' . config('app.name'))

@section('content')
@php
    $statusKeyMap = ['Open' => 'status_open', 'In Progress' => 'status_in_progress', 'Resolved' => 'status_resolved', 'Closed' => 'status_closed'];
    $escKeyMap = ['Normal' => 'tickets_escalation_normal', 'Sedang' => 'tickets_escalation_medium', 'Tinggi' => 'tickets_escalation_high', 'KRITIS' => 'tickets_escalation_critical'];
@endphp
<div class="space-y-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-50">{{ __('tickets_index_heading') }}</h1>
            <p class="text-sm text-slate-400 mt-1 max-w-2xl">{{ __('tickets_index_intro') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('export.rekap') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/50 bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-300 hover:bg-emerald-500/20">📥 {{ __('excel_rekap') }}</a>
            <a href="{{ route('export.per-ticket') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-sky-500/50 bg-sky-500/10 px-3 py-2 text-xs font-medium text-sky-300 hover:bg-sky-500/20">📥 {{ __('tickets_excel_all') }}</a>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-3.5 py-2 text-xs font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition">+ {{ __('tickets_new_manual') }}</a>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-3 md:p-4 shadow-xl shadow-black/30">
        <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
            <div class="flex flex-wrap gap-2 text-[11px]">
                <select name="status" class="bg-slate-900/80 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                    <option value="all" {{ ($filters['status'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('tickets_filter_status_all') }}</option>
                    <option value="Open" {{ ($filters['status'] ?? '') === 'Open' ? 'selected' : '' }}>{{ __('status_open') }}</option>
                    <option value="In Progress" {{ ($filters['status'] ?? '') === 'In Progress' ? 'selected' : '' }}>{{ __('status_in_progress') }}</option>
                    <option value="Resolved" {{ ($filters['status'] ?? '') === 'Resolved' ? 'selected' : '' }}>{{ __('status_resolved') }}</option>
                    <option value="Closed" {{ ($filters['status'] ?? '') === 'Closed' ? 'selected' : '' }}>{{ __('status_closed') }}</option>
                </select>
                <select name="urgency" class="bg-slate-900/80 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                    <option value="all" {{ ($filters['urgency'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('tickets_filter_escalation_all') }}</option>
                    <option value="Normal" {{ ($filters['urgency'] ?? '') === 'Normal' ? 'selected' : '' }}>{{ __('tickets_escalation_normal') }}</option>
                    <option value="Sedang" {{ ($filters['urgency'] ?? '') === 'Sedang' ? 'selected' : '' }}>{{ __('tickets_escalation_medium') }}</option>
                    <option value="Tinggi" {{ ($filters['urgency'] ?? '') === 'Tinggi' ? 'selected' : '' }}>{{ __('tickets_escalation_high') }}</option>
                    <option value="KRITIS" {{ ($filters['urgency'] ?? '') === 'KRITIS' ? 'selected' : '' }}>{{ __('tickets_escalation_critical') }}</option>
                </select>
                <select name="level" class="bg-slate-900/80 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                    <option value="all" {{ ($filters['level'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('tickets_filter_level_all') }}</option>
                    <option value="Mikro" {{ ($filters['level'] ?? '') === 'Mikro' ? 'selected' : '' }}>Mikro</option>
                    <option value="Meso" {{ ($filters['level'] ?? '') === 'Meso' ? 'selected' : '' }}>Meso</option>
                    <option value="Makro" {{ ($filters['level'] ?? '') === 'Makro' ? 'selected' : '' }}>Makro</option>
                </select>
            </div>
            <div class="flex gap-2 flex-1 md:max-w-xs">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('tickets_search_placeholder') }}"
                    class="flex-1 bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                <button type="submit" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-xs text-slate-200 hover:bg-slate-700">{{ __('tickets_filter_btn') }}</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left border-separate border-spacing-y-1">
                <thead>
                    <tr class="text-[11px] uppercase tracking-wide text-slate-400">
                        <th class="px-3 py-1.5">{{ __('tickets_th_ticket') }}</th>
                        <th class="px-3 py-1.5">{{ __('tickets_th_status') }}</th>
                        <th class="px-3 py-1.5">{{ __('tickets_th_level') }}</th>
                        <th class="px-3 py-1.5">{{ __('tickets_th_category') }}</th>
                        <th class="px-3 py-1.5">{{ __('tickets_th_score') }}</th>
                        <th class="px-3 py-1.5">{{ __('tickets_th_source') }}</th>
                        <th class="px-3 py-1.5">{{ __('tickets_th_recorded') }}</th>
                        <th class="px-3 py-1.5 text-right">{{ __('tickets_th_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr class="bg-slate-900/60 border border-slate-800/80 rounded-xl">
                            <td class="px-3 py-2.5 align-top">
                                <a href="{{ route('tickets.show', $t->id) }}" class="text-xs font-medium text-sky-300 hover:text-sky-200">{{ $t->ticket_id }}</a>
                                <div class="text-[11px] text-slate-200 mt-0.5 line-clamp-2">{{ $t->judul_sinyal }}</div>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $t->nama_lokasi ?? __('tickets_location_unknown') }}</div>
                            </td>
                            <td class="px-3 py-2.5 align-top">
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
                            <td class="px-3 py-2.5 align-top text-[11px] text-slate-200">{{ $t->level_sinyal ?? '-' }}</td>
                            <td class="px-3 py-2.5 align-top text-[11px] text-slate-200">{{ $t->kategori_sinyal ?? '-' }}</td>
                            <td class="px-3 py-2.5 align-top text-[11px]">
                                <div class="font-semibold text-slate-100">{{ $t->skor_urgensi_hukum ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">max {{ $t->skor_urgensi_tertinggi ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-2.5 align-top text-[11px] text-slate-200">
                                <span class="uppercase text-[10px]">{{ $t->jenis_sumber ?? '-' }}</span>
                                @if($t->nama_pelapor)<div class="text-[10px] text-slate-500">{{ $t->nama_pelapor }}</div>@endif
                            </td>
                            <td class="px-3 py-2.5 align-top text-[11px] text-slate-200">{{ $t->waktu_catat ? $t->waktu_catat->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-3 py-2.5 align-top text-right">
                                <a href="{{ route('tickets.show', $t->id) }}" class="inline-flex items-center rounded-lg border border-slate-700/80 bg-slate-900/70 px-2.5 py-1 text-[11px] font-medium text-slate-100 hover:bg-slate-800 hover:border-amber-400/60 hover:text-amber-200 transition">{{ __('tickets_edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-3 py-4 text-center text-slate-500">{{ __('tickets_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="mt-4 flex items-center justify-between gap-2 text-xs text-slate-400">
                <span>{{ __('tickets_page_info', ['current' => $tickets->currentPage(), 'last' => $tickets->lastPage(), 'total' => $tickets->total()]) }}</span>
                <div class="flex items-center gap-1">
                    @if($tickets->onFirstPage())
                        <span class="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-900/80 text-slate-500 opacity-40">{{ __('tickets_prev') }}</span>
                    @else
                        <a href="{{ $tickets->previousPageUrl() }}" class="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-900/80 text-slate-200 hover:bg-slate-800">{{ __('tickets_prev') }}</a>
                    @endif
                    @if($tickets->hasMorePages())
                        <a href="{{ $tickets->nextPageUrl() }}" class="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-900/80 text-slate-200 hover:bg-slate-800">{{ __('tickets_next') }}</a>
                    @else
                        <span class="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-900/80 text-slate-500 opacity-40">{{ __('tickets_next') }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
