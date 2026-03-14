<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/ozj-logo.png') }}">
    <title>{{ __('track_title') }} - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-950 flex flex-col items-center justify-center px-4 py-8">
    @include('partials.locale-switcher')
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <div class="inline-flex h-12 w-12 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 items-center justify-center shadow-lg shadow-amber-500/30 mb-3">
                <span class="text-lg font-black tracking-tight text-slate-950">OZJ</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-100">{{ __('track_title') }}</h1>
            <p class="text-sm text-slate-400 mt-1">{{ __('track_subtitle') }}</p>
        </div>

        <form method="GET" action="{{ route('track') }}" class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30 space-y-3">
            <label class="block text-xs text-slate-300">{{ __('ticket_number') }}</label>
            <div class="flex gap-2">
                <input type="text" name="ticket_id" value="{{ request('ticket_id', $ticket->ticket_id ?? '') }}" placeholder="{{ __('ticket_number_placeholder') }}"
                    class="flex-1 bg-slate-950/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                <button type="submit" class="rounded-lg bg-gradient-to-r from-amber-400 to-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg hover:brightness-110 transition">{{ __('check') }}</button>
            </div>
        </form>

        @if((request()->filled('ticket_id') || request()->route('ticket_id')) && !$ticket)
            <div class="rounded-lg bg-rose-950/40 border border-rose-800/60 px-4 py-3 text-sm text-rose-200">{{ __('ticket_not_found') }}</div>
        @endif

        @if($ticket)
            @php
                $statusLabels = [
                    'Open' => __('status_open'),
                    'In Progress' => __('status_in_progress'),
                    'Resolved' => __('status_resolved'),
                    'Closed' => __('status_closed'),
                ];
            @endphp
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-2 gap-2">
                    <span class="text-xs text-slate-400">{{ __('ticket_number') }}</span>
                    <span class="font-mono text-sm font-medium text-amber-300 truncate">{{ $ticket->ticket_id }}</span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ url()->current() }}').then(() => alert({{ json_encode(__('copy_link_done')) }}))" class="shrink-0 text-xs text-amber-400 hover:text-amber-300">{{ __('copy_link') }}</button>
                </div>
                @if($ticket->judul_sinyal)
                    <div>
                        <span class="text-xs text-slate-400">{{ __('title_label') }}</span>
                        <p class="text-sm text-slate-200 mt-0.5">{{ $ticket->judul_sinyal }}</p>
                    </div>
                @endif
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-xs text-slate-400">{{ __('status_label') }}</span>
                        <p class="text-sm text-slate-200">{{ $statusLabels[$ticket->status_ticket] ?? $ticket->status_ticket }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400">{{ __('urgency_label') }}</span>
                        <p class="text-sm text-slate-200">{{ $ticket->status_eskalasi ?? $ticket->urgensi_sinyal ?? '—' }}</p>
                    </div>
                </div>
                @if($ticket->waktu_catat)
                    <div>
                        <span class="text-xs text-slate-400">{{ __('report_time') }}</span>
                        <p class="text-sm text-slate-300">{{ $ticket->waktu_catat->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
                @if($ticket->nama_pelapor)
                    <div>
                        <span class="text-xs text-slate-400">{{ __('reporter') }}</span>
                        <p class="text-sm text-slate-300">{{ $ticket->nama_pelapor }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
    <p class="mt-6 text-xs text-slate-500">{{ __('track_footer') }} <a href="{{ route('login') }}" class="text-amber-400/80 hover:text-amber-300">{{ __('login_admin') }}</a></p>
</body>
</html>
