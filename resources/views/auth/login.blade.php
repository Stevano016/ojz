<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('login') }} - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center px-4">
    @include('partials.locale-switcher')
    <div class="max-w-md w-full">
        <div class="mb-6 flex items-center justify-center gap-2">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <span class="text-sm font-black tracking-tight text-slate-950">OZJ</span>
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-100">{{ __('app_name') }} System</span>
                <span class="text-xs text-slate-400">{{ __('app_tagline') }} Youth Care</span>
            </div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-2xl shadow-black/40 backdrop-blur-xl">
            <h1 class="text-lg font-semibold text-slate-50 mb-1">{{ __('login_heading') }}</h1>
            <p class="text-sm text-slate-400 mb-5">{{ __('login_subtitle') }}</p>
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">{{ __('email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', 'admin@ozj.nl') }}" required autofocus autocomplete="email"
                        class="w-full rounded-lg bg-slate-900/80 border border-slate-700 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60" />
                    @error('email')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">{{ __('password') }}</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                        class="w-full rounded-lg bg-slate-900/80 border border-slate-700 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60" />
                </div>
                <button type="submit" class="w-full mt-2 inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition">
                    {{ __('sign_in') }}
                </button>
            </form>
            <div class="mt-4 text-[11px] text-slate-500">{{ __('login_demo') }} <span class="font-mono">admin@ozj.nl / password</span></div>
            <div class="mt-4 pt-4 border-t border-slate-700/80">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition">
                    <span aria-hidden="true">←</span>
                    {{ __('back_to_home') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
