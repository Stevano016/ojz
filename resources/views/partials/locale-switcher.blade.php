@php
    $locales = [
        'id' => ['flag' => '🇮🇩', 'name' => 'Indonesia'],
        'en' => ['flag' => '🇬🇧', 'name' => 'English'],
        'nl' => ['flag' => '🇳🇱', 'name' => 'Nederlands'],
    ];
    $current = app()->getLocale();
    if (!isset($locales[$current])) {
        $current = 'en';
    }
@endphp
<div class="fixed top-12 right-4 z-50 flex items-center gap-2" role="group" aria-label="Language">
    @foreach($locales as $code => $info)
        <a href="{{ route('locale.switch', $code) }}"
           class="flex h-10 w-10 items-center justify-center rounded-full border-2 bg-slate-900/90 shadow-lg backdrop-blur-sm transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-amber-400 {{ $current === $code ? 'border-amber-400 ring-2 ring-amber-400/30' : 'border-slate-600 hover:border-slate-500' }}"
           title="{{ $info['name'] }}"
           aria-label="{{ $info['name'] }}">
            <span class="text-lg leading-none" aria-hidden="true">{{ $info['flag'] }}</span>
        </a>
    @endforeach
</div>
