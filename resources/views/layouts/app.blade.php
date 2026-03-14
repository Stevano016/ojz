<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            dark: 'class',
            theme: { extend: {} }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    @include('partials.locale-switcher')
    <div class="flex min-h-screen">
        <aside class="w-60 bg-slate-900/80 border-r border-slate-800/60 hidden md:flex flex-col">
            <div class="px-4 py-5 border-b border-slate-800/60">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-2 text-center">
                    <img src="{{ asset('images/ozj-logo.png') }}" alt="OZJ - AI Signal Intelligence" class="h-14 w-auto object-contain" />
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold leading-tight text-slate-200">{{ __('app_name') }}</span>
                        <span class="text-[10px] text-slate-500 leading-tight">{{ __('app_tagline') }}</span>
                    </div>
                </a>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-amber-300' : 'text-slate-300 hover:bg-white/10' }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> {{ __('nav_dashboard') }}
                </a>
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tickets.*') && !request()->routeIs('tickets.create') ? 'bg-slate-800 text-amber-300' : 'text-slate-300 hover:bg-white/10' }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span> {{ __('nav_tickets') }}
                </a>
                <a href="{{ route('tickets.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tickets.create') ? 'bg-slate-800 text-amber-300' : 'text-slate-300 hover:bg-white/10' }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> {{ __('nav_new_ticket') }}
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-amber-300' : 'text-slate-300 hover:bg-white/10' }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-violet-400"></span> {{ __('nav_settings') }}
                </a>
            </nav>
            <div class="px-4 py-4 border-t border-slate-800/60 text-xs text-slate-400 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="h-7 w-7 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-semibold shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-medium text-slate-200 truncate">{{ auth()->user()->name ?? 'User' }}</span>
                        <span class="text-[11px] truncate">{{ auth()->user()->email }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-[11px] text-rose-300 hover:text-rose-200">{{ __('logout') }}</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="md:hidden pl-4 pr-24 py-3 border-b border-slate-800/60 bg-slate-950/80 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/ozj-logo.png') }}" alt="OZJ" class="h-9 w-auto object-contain" />
                    <span class="text-sm font-semibold">{{ __('app_name') }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="text-[11px] text-rose-300 hover:text-rose-200">{{ __('logout') }}</button>
                </form>
            </header>
            <div class="hidden md:flex items-center justify-end gap-3 pl-4 pr-24 py-2 border-b border-slate-800/60 bg-slate-950/50">
                <span class="text-[11px] text-slate-400 truncate max-w-[12rem]">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="text-xs text-rose-300 hover:text-rose-200 font-medium px-2.5 py-1 rounded-md hover:bg-rose-500/10 transition">{{ __('logout') }}</button>
                </form>
            </div>
            <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-6 md:py-8">
                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-emerald-500/20 border border-emerald-500/40 px-4 py-2 text-sm text-emerald-200">{{ session('success') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
