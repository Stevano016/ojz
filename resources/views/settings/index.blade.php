@extends('layouts.app')

@section('title', __('settings_title') . ' - ' . config('app.name'))

@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-xl font-semibold text-slate-50">{{ __('settings_heading') }}</h1>
        <p class="text-sm text-slate-400 mt-1 max-w-2xl">{{ __('settings_intro') }}</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-emerald-950/50 border border-emerald-700/60 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-rose-950/50 border border-rose-700/60 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
    @endif
    @if(!empty($loadError))
        <div class="rounded-lg bg-amber-950/50 border border-amber-700/60 px-4 py-3 text-sm text-amber-200">
            {{ __('settings_load_error') }} <span class="text-amber-300/80 text-xs font-mono">{{ $loadError }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 md:p-5 shadow-xl shadow-black/30 space-y-4 max-w-xl">
        @csrf
        @method('PUT')
        <div>
            <label for="phone_number" class="block text-xs font-medium text-slate-300 mb-1.5">{{ __('settings_phone_number') }}</label>
            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $settings['phone_number'] ?? '') }}"
                placeholder="e.g. +31612345678"
                class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60" />
            @error('phone_number')
                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">{{ __('settings_email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email', $settings['email'] ?? '') }}"
                placeholder="contact@example.com"
                class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60" />
            @error('email')
                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="whatsapps" class="block text-xs font-medium text-slate-300 mb-1.5">{{ __('settings_whatsapps') }}</label>
            <input type="text" name="whatsapps" id="whatsapps" value="{{ old('whatsapps', $settings['whatsapps'] ?? '') }}"
                placeholder="e.g. +31612345678 or WhatsApp number"
                class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60" />
            @error('whatsapps')
                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>
        <div class="pt-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition">
                {{ __('settings_save') }}
            </button>
        </div>
    </form>
</div>
@endsection
