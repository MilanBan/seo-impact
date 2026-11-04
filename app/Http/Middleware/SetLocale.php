<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');
        abort_unless(in_array($locale, config('site.locales'), true), 404);
        app()->setLocale($locale);

        if ($request->user() && $request->user()->preferred_locale !== $locale) {
            $request->user()->forceFill(['preferred_locale' => $locale])->save();
        }

        return $next($request);
    }
}
