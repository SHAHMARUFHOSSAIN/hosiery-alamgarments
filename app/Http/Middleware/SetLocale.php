<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale', config('app.locale'));

        if (auth()->check() && auth()->user()->language) {
            $locale = auth()->user()->language;
        }

        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        view()->composer('*', function ($view) use ($locale) {
            $view->with('currentLocale', $locale);
        });

        return $next($request);
    }
}
