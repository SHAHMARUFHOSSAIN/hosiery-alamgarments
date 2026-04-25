<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (Auth::user()->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->userDashboard();
    }

    private function adminDashboard(): View
    {
        $stats = [
            'totalCustomers' => Customer::count(),
            'totalBills' => Bill::count(),
            'totalDues' => Due::where('status', 'pending')->sum('amount'),
            'todayDues' => Due::whereDate('due_date', now()->toDateString())
                ->where('status', 'pending')
                ->count(),
        ];

        $recentBills = Bill::with(['customer', 'user'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentDues = Due::with(['customer', 'creator'])
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $users = User::where('role', 'user')->get(['id', 'name']);

        return view('dashboard.admin', compact('stats', 'recentBills', 'recentDues', 'users'));
    }

    private function userDashboard(): View
    {
        $stats = [
            'totalCustomers' => Customer::where('created_by', Auth::id())->count(),
            'totalBills' => Bill::where('user_id', Auth::id())->count(),
            'totalDues' => Due::where('created_by', Auth::id())
                ->where('status', 'pending')
                ->sum('amount'),
            'todayDues' => Due::where('created_by', Auth::id())
                ->whereDate('due_date', now()->toDateString())
                ->where('status', 'pending')
                ->count(),
        ];

        $recentBills = Bill::with(['customer'])
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentDues = Due::with(['customer'])
            ->where('created_by', Auth::id())
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        return view('dashboard.user', compact('stats', 'recentBills', 'recentDues'));
    }
}