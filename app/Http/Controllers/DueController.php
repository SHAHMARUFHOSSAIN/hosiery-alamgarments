<?php

namespace App\Http\Controllers;

use App\Models\Due;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DueController extends Controller
{
    public function index(): View
    {
        $dues = Due::with('customer', 'bill')->get();
        return view('dues.index', compact('dues'));
    }

    public function dailyReport(): View
    {
        $todayDues = Due::with('customer', 'creator')
            ->whereDate('due_date', now()->toDateString())
            ->where('status', 'pending')
            ->get();
        return view('dues.daily-report', compact('todayDues'));
    }

    public function markPaid($id): \Illuminate\Http\RedirectResponse
    {
        $due = Due::find($id);
        if ($due) {
            $due->update(['status' => 'paid']);
            return redirect()->back()->with('success', 'Marked as paid');
        }
        return redirect()->back()->with('error', 'Due not found');
    }
}