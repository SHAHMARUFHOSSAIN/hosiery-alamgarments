<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (Auth::user()->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->userDashboard($request);
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

        $recentDues = Due::with(['customer', 'creator', 'duePayments.user'])
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $users = User::where('role', 'user')->get(['id', 'name']);

        return view('dashboard.admin', compact('stats', 'recentBills', 'recentDues', 'users'));
    }

    private function userDashboard(Request $request): View
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $filter = $request->get('sales_filter', '7days');
        if ($filter === 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
            $label = 'Today';
        } elseif ($filter === 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
            $label = 'Yesterday';
        } else {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
            $label = 'Last 7 Days';
        }

        $totalCredit = MainBalance::where('branch_id', $userId)->where('type', 'credit')->sum('amount');
        $totalDebit = MainBalance::where('branch_id', $userId)->where('type', 'debit')->sum('amount');
        $mainBalance = $totalCredit - $totalDebit;

        $startOfMonth = now()->startOfMonth()->toDateString();
        $thisMonthSales = Bill::where('user_id', $userId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('bill_amount');
        $thisMonthDiscount = Bill::where('user_id', $userId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('discount');
        $todaySales = Bill::where('user_id', $userId)->whereDate('created_at', $today)->sum('bill_amount');
        $totalSales = Bill::where('user_id', $userId)->sum('bill_amount');

        $paymentBreakdown = Bill::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with('payments')
            ->get()
            ->pluck('payments')
            ->flatten()
            ->groupBy('payment_type')
            ->map(fn($payments) => $payments->sum('amount'));

        $stats = [
            'totalCustomers' => Customer::where('created_by', $userId)->count(),
            'totalBills' => Bill::where('user_id', $userId)->count(),
            'totalDues' => Due::where('created_by', $userId)->where('status', 'pending')->sum('amount'),
            'todayDues' => Due::where('created_by', $userId)->whereDate('due_date', $today)->where('status', 'pending')->count(),
            'mainBalance' => $mainBalance,
            'todaySales' => $todaySales,
            'thisMonthSales' => $thisMonthSales,
            'thisMonthDiscount' => $thisMonthDiscount,
            'totalSales' => $totalSales,
            'cashSales' => $paymentBreakdown->get('cash', 0),
            'checkSales' => $paymentBreakdown->get('check', 0),
            'ttSales' => $paymentBreakdown->get('tt', 0),
            'cardSales' => $paymentBreakdown->get('card', 0),
            'dueSales' => $paymentBreakdown->get('due', 0),
        ];

        $recentBills = Bill::with(['customer'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentDues = Due::with(['customer', 'duePayments.user'])
            ->where('created_by', $userId)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $recentTransactions = MainBalance::where('branch_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $dailySales = Bill::where('user_id', $userId)
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->selectRaw('DATE(created_at) as date, SUM(bill_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyLabels = [];
        $dailyValues = [];
        $day = now()->subDays(7);
        while ($day <= now()) {
            $dateStr = $day->format('Y-m-d');
            $dailyLabels[] = $day->format('M d');
            $match = $dailySales->firstWhere('date', $dateStr);
            $dailyValues[] = $match ? (float) $match->total : 0;
            $day->addDay();
        }

        $todayBills = Bill::with(['customer'])
            ->where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->get();

        $weekBills = Bill::with(['customer'])
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($bill) {
                return $bill->created_at->format('Y-m-d');
            });

        $totalFiltered = $weekBills->flatten()->sum('bill_amount');

        return view('dashboard.user', compact('stats', 'recentBills', 'recentDues', 'recentTransactions', 'dailyLabels', 'dailyValues', 'todayBills', 'weekBills', 'filter', 'label', 'totalFiltered'));
    }
}