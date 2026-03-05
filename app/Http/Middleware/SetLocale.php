<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', 'et');

        if (!in_array($locale, ['et', 'en', 'fi'], true)) {
            $locale = 'et';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
