<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('home_title') }} - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-950 flex flex-col items-center justify-center px-4 py-8">
    @include('partials.locale-switcher')
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <div class="inline-flex h-14 w-14 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 items-center justify-center shadow-lg shadow-amber-500/30 mb-4">
                <span class="text-xl font-black tracking-tight text-slate-950">OZJ</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-100">{{ __('home_title') }}</h1>
            <p class="text-sm text-slate-400 mt-2">{{ __('home_subtitle') }}</p>
        </div>

        <form method="GET" action="{{ route('track') }}" class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-5 shadow-xl shadow-black/30 space-y-4">
            <label for="ticket_id" class="block text-sm font-medium text-slate-300">{{ __('ticket_number') }}</label>
            <input type="text" id="ticket_id" name="ticket_id" value="{{ request('ticket_id') }}"
                placeholder="{{ __('ticket_number_placeholder') }}"
                class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400/50" />
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-amber-400 to-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 shadow-lg hover:brightness-110 transition">
                    {{ __('search_ticket') }}
                </button>
                <a href="{{ route('login') }}" class="flex-1 rounded-lg border border-slate-600 bg-slate-800/80 px-4 py-3 text-sm font-medium text-slate-200 text-center hover:bg-slate-700/80 transition">
                    {{ __('login') }}
                </a>
            </div>
        </form>
    </div>
</body>
</html>
