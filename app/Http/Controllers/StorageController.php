<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function __invoke(string $path)
    {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            abort(404);
        }
        return response()->file($fullPath);
    }
}
