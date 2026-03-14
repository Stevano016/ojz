<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request. Set app locale from session (must run after StartSession).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));
        if (in_array($locale, ['id', 'en', 'nl'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
