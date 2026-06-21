<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Payment;
use App\Models\TodaySalesReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (Auth::user()->isAdmin()) {
            return $this->adminDashboard($request);
        }

        return $this->userDashboard($request);
    }

    private function adminDashboard(Request $request): View
    {
        $dateFrom = $request->get('date_from', now()->subDay()->startOfDay()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $stats = [
            'totalCustomers' => Customer::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
            'totalBills' => Bill::whereBetween('report_date', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
            'totalDues' => Due::where('status', 'pending')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('amount'),
            'todayDues' => Due::whereDate('due_date', now()->toDateString())
                ->where('status', 'pending')
                ->count(),
            'totalSales' => Bill::whereBetween('report_date', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('bill_amount'),
        ];

        $recentBills = Bill::with(['customer', 'user', 'editor'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentDues = Due::with(['customer', 'creator', 'duePayments.user'])
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $users = User::where('role', 'user')->get(['id', 'name']);

        $userStats = [];
        $chartLabels = [];
        $chartSales = [];
        $chartBills = [];
        $chartDues = [];
        $chartCustomers = [];
        $userPerformance = [];
        $userList = User::where('role', 'user')->get();
        foreach ($userList as $user) {
            $billQuery = Bill::where('user_id', $user->id)
                ->whereBetween('report_date', [$dateFrom, $dateTo . ' 23:59:59']);
            $uBills = (clone $billQuery)->count();
            $uSales = (clone $billQuery)->sum('bill_amount');
            $uDiscount = (clone $billQuery)->sum('discount');

            $uDues = Due::where('created_by', $user->id)
                ->where('status', 'pending')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('amount');

            $uCustomers = Customer::where('created_by', $user->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count();

            $uTodayDues = Due::where('created_by', $user->id)
                ->whereDate('due_date', now()->toDateString())
                ->where('status', 'pending')
                ->count();

            $userStats[] = [
                'id' => $user->id,
                'name' => $user->name,
                'bills' => $uBills,
                'sales' => $uSales,
                'dues' => $uDues,
                'customers' => $uCustomers,
                'todayDues' => $uTodayDues,
            ];

            $uReportDiscount = TodaySalesReport::where('user_id', $user->id)
                ->where('status', 'closed')
                ->whereBetween('report_date', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('discount_amt');

            $userPerformance[] = [
                'name' => $user->name,
                'sales' => $uSales,
                'bills' => $uBills,
                'discount' => $uDiscount,
                'report_discount' => $uReportDiscount,
            ];

            $chartLabels[] = $user->name;
            $chartSales[] = (float) $uSales;
            $chartBills[] = $uBills;
            $chartDues[] = (float) $uDues;
            $chartCustomers[] = $uCustomers;
        }

        $pendingCheques = Payment::with(['bill.customer', 'bill.user'])
            ->where('payment_type', 'check')
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('encashed_amount')
                  ->orWhere('encashed_amount', '<', \DB::raw('check_amount'));
            })
            ->orderBy('check_date', 'asc')
            ->limit(10)
            ->get();

        usort($userPerformance, function ($a, $b) {
            return $b['sales'] <=> $a['sales'];
        });

        return view('dashboard.admin', compact(
            'stats', 'recentBills', 'recentDues', 'users',
            'userStats', 'chartLabels', 'chartSales', 'chartBills', 'chartDues', 'chartCustomers',
            'userPerformance', 'dateFrom', 'dateTo', 'pendingCheques'
        ));
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
        $thisMonthSales = Bill::where('user_id', $userId)->whereMonth('report_date', now()->month)->whereYear('report_date', now()->year)->sum('bill_amount');
        $thisMonthDiscount = Bill::where('user_id', $userId)->whereMonth('report_date', now()->month)->whereYear('report_date', now()->year)->sum('discount');
        $todaySales = Bill::where('user_id', $userId)->whereDate('report_date', $today)->sum('bill_amount');
        $totalSales = Bill::where('user_id', $userId)->sum('bill_amount');

        $paymentBreakdown = Bill::where('user_id', $userId)
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
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
            'dueSales' => Due::where('created_by', $userId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereHas('bill.payments', fn($q) => $q->where('payment_type', 'due'))
                ->sum('original_amount'),
        ];

        $recentBills = Bill::with(['customer', 'editor'])
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
            ->whereBetween('report_date', [now()->subDays(7), now()])
            ->selectRaw('DATE(report_date) as date, SUM(bill_amount) as total, COUNT(*) as count')
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
            ->whereDate('report_date', $today)
            ->orderBy('id', 'desc')
            ->get();

        $weekBills = Bill::with(['customer', 'editor'])
            ->where('user_id', $userId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy(function ($bill) {
                return $bill->report_date?->format('Y-m-d') ?? 'Unknown';
            });

        $pendingCheques = Payment::with(['bill.customer'])
            ->where('payment_type', 'check')
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('encashed_amount')
                  ->orWhere('encashed_amount', '<', \DB::raw('check_amount'));
            })
            ->whereHas('bill', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderBy('check_date', 'asc')
            ->limit(10)
            ->get();

        $totalFiltered = $weekBills->flatten()->sum('bill_amount');

        return view('dashboard.user', compact('stats', 'recentBills', 'recentDues', 'recentTransactions', 'dailyLabels', 'dailyValues', 'todayBills', 'weekBills', 'filter', 'label', 'totalFiltered', 'pendingCheques'));
    }
}