<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['en', 'bn'])) {
            abort(400);
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        if (auth()->check()) {
            auth()->user()->update(['language' => $locale]);
        }

        return redirect()->back();
    }
}
