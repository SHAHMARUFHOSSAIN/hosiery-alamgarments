<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\CheckEncashment;
use App\Models\Customer;
use App\Models\Due;
use App\Models\DuePayment;
use App\Models\Payment;
use App\Models\PreviousDue;
use App\Models\PreviousDuePayment;
use App\Models\TodaySalesReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $users = User::where('role', 'user')->get(['id', 'name']);

        $query = Bill::query();
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }
        $totalSales = $query->sum('bill_amount');

        $dueQuery = Due::query();
        if (!Auth::user()->isAdmin()) {
            $dueQuery->where('created_by', Auth::id());
        }
        $totalDues = (clone $dueQuery)->where('status', 'pending')->sum('amount');
        $paidDues = (clone $dueQuery)->where('status', 'paid')->sum('amount');

        $prevDueQuery = PreviousDue::query();
        if (!Auth::user()->isAdmin()) {
            $prevDueQuery->where('created_by', Auth::id());
        }
        $totalPrevDues = $prevDueQuery->clone()->where('status', 'pending')->sum('amount');
        $totalPrevCollected = PreviousDuePayment::whereIn('previous_due_id', $prevDueQuery->clone()->select('id'))->sum('amount');

        $duePaymentCollection = DuePayment::whereHas('due.bill', function ($q) {
            if (!Auth::user()->isAdmin()) {
                $q->where('user_id', Auth::id());
            }
        })->sum('amount');

        $paymentQuery = Payment::query();
        if (!Auth::user()->isAdmin()) {
            $paymentQuery->whereHas('bill', fn($q) => $q->where('user_id', Auth::id()));
        }

        $paymentTotals = (clone $paymentQuery)
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'cash' THEN amount ELSE 0 END), 0) as cash_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'check' THEN amount ELSE 0 END), 0) as check_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'tt' THEN amount ELSE 0 END), 0) as tt_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'card' THEN amount ELSE 0 END), 0) as card_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'due' THEN amount ELSE 0 END), 0) as due_total")
            ->first();

        $totalDiscount = Bill::query()
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('user_id', Auth::id()))
            ->sum('discount');

        $totalReportDiscount = TodaySalesReport::where('status', 'closed')
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('user_id', Auth::id()))
            ->sum('discount_amt');

        $duePayQuery = DuePayment::query();
        if (Schema::hasColumn('due_payments', 'status')) {
            $duePayQuery->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'));
        } else {
            $duePayQuery->where('payment_type', '!=', 'check');
        }
        $duePayDiscount = $duePayQuery
            ->whereHas('due.bill', function ($q) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('user_id', Auth::id());
                }
            })->sum('discount');

        $prevDuePayQuery = PreviousDuePayment::query();
        if (Schema::hasColumn('previous_due_payments', 'status')) {
            $prevDuePayQuery->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'));
        } else {
            $prevDuePayQuery->where('payment_type', '!=', 'check');
        }
        $prevDuePayDiscount = $prevDuePayQuery
            ->whereHas('previousDue', function ($q) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('created_by', Auth::id());
                }
            })->sum('discount');

        $encashDiscountQuery = CheckEncashment::whereHas('payment.bill', function ($q) {
            if (!Auth::user()->isAdmin()) {
                $q->where('user_id', Auth::id());
            }
        });
        $encashDiscount = (clone $encashDiscountQuery)->sum('discount');

        $totalDueDiscount = ($duePayDiscount ?? 0) + ($prevDuePayDiscount ?? 0) + ($encashDiscount ?? 0);

        $encashQuery = Payment::query();
        if (!Auth::user()->isAdmin()) {
            $encashQuery->whereHas('bill', fn($q) => $q->where('user_id', Auth::id()));
        }

        $chequeEncashed = (clone $encashQuery)
            ->where('payment_type', 'check')
            ->sum('encashed_amount');

        $cardEncashed = (clone $encashQuery)
            ->where('payment_type', 'card')
            ->where('status', 'encashed')
            ->sum('amount');

        $mainBalance = ($paymentTotals->cash_total ?? 0)
            + ($paymentTotals->tt_total ?? 0)
            + ($chequeEncashed ?? 0)
            + ($cardEncashed ?? 0)
            + ($totalPrevCollected ?? 0)
            + ($duePaymentCollection ?? 0);

        $totalDiscountAll = ($totalDiscount ?? 0) + ($totalReportDiscount ?? 0) + ($totalDueDiscount ?? 0);

        return view('reports.index', compact(
            'users', 'totalSales', 'totalDues', 'paidDues', 'totalPrevDues', 'totalPrevCollected',
            'paymentTotals', 'totalDiscount', 'totalReportDiscount', 'totalDueDiscount', 'totalDiscountAll', 'mainBalance', 'duePaymentCollection'
        ));
    }

    public function sales(Request $request): View
    {
        $query = Bill::with(['customer', 'user', 'editor']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $sortField = $request->get('sort', 'report_date');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['bill_no', 'bill_amount', 'discount', 'report_date'];

        if ($sortField === 'net') {
            $query->orderByRaw('(bill_amount - discount) ' . $sortDirection);
        } elseif (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('report_date', 'desc');
        }

        $bills = $query->paginate(20);
        $totalAmount = Bill::when(Auth::user()->isAdmin(), function ($q) use ($request) {
                if ($request->filled('user_id')) $q->where('user_id', $request->user_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('report_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('report_date', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('bill_no', 'like', "%{$search}%")
                          ->orWhere('shop_name', 'like', "%{$search}%")
                          ->orWhere('bill_man', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($cq) use ($search) {
                              $cq->where('name', 'like', "%{$search}%")
                                 ->orWhere('mobile', 'like', "%{$search}%");
                          });
                });
            })
            ->sum('bill_amount');

        $totalDiscount = Bill::when(Auth::user()->isAdmin(), function ($q) use ($request) {
                if ($request->filled('user_id')) $q->where('user_id', $request->user_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('report_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('report_date', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('bill_no', 'like', "%{$search}%")
                          ->orWhere('shop_name', 'like', "%{$search}%")
                          ->orWhere('bill_man', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($cq) use ($search) {
                              $cq->where('name', 'like', "%{$search}%")
                                 ->orWhere('mobile', 'like', "%{$search}%");
                          });
                });
            })
            ->sum('discount');

        $users = User::where('role', 'user')->get(['id', 'name']);

        return view('reports.sales', compact('bills', 'totalAmount', 'totalDiscount', 'users'));
    }

    public function dues(Request $request): View
    {
        $users = User::where('role', 'user')->get(['id', 'name']);

        $type = in_array($request->get('type', 'all'), ['all', 'due', 'cheque'])
            ? $request->get('type', 'all')
            : 'all';

        $applyUserScope = function ($q) use ($request) {
            if (Auth::user()->isAdmin()) {
                if ($request->filled('user_id')) {
                    $q->where('bills.user_id', $request->user_id);
                }
            } else {
                $q->where('bills.user_id', Auth::id());
            }
        };

        $applyScope = function ($q) use ($request, $applyUserScope) {
            $applyUserScope($q);
            if ($request->filled('date_from')) {
                $q->whereDate('bills.report_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $q->whereDate('bills.report_date', '<=', $request->date_to);
            }
        };

        // Single date / last date => bill-level detail view.
        // A range with distinct dates, or ?all=1 => aggregated date-wise list.
        $dateFrom = $request->filled('date_from') ? $request->date_from : null;
        $dateTo = $request->filled('date_to') ? $request->date_to : null;

        $detailMode = !$request->boolean('all')
            && ($dateTo === null || $dateFrom === null || $dateFrom === $dateTo);

        if ($detailMode) {
            $latestQuery = Bill::query();
            $applyUserScope($latestQuery);
            $detailDate = $dateTo ?: $dateFrom ?: $latestQuery->max('report_date');

            $bills = collect();
            $duePerBill = collect();
            $chequePerBill = collect();

            if ($detailDate) {
                $billsQuery = Bill::with(['customer', 'user'])
                    ->whereDate('report_date', $detailDate);
                $applyUserScope($billsQuery);
                $bills = $billsQuery->orderBy('id')->get();

                $billIds = $bills->pluck('id');

                $duePerBill = Due::whereIn('bill_id', $billIds)
                    ->selectRaw('bill_id, COALESCE(SUM(original_amount), 0) as total')
                    ->groupBy('bill_id')
                    ->pluck('total', 'bill_id');

                $chequePerBill = Payment::whereIn('bill_id', $billIds)
                    ->where('payment_type', 'check')
                    ->selectRaw('bill_id, COALESCE(SUM(check_amount), 0) as total')
                    ->groupBy('bill_id')
                    ->pluck('total', 'bill_id');

                $bills = $bills->filter(function ($bill) use ($duePerBill, $chequePerBill) {
                    return (float) ($duePerBill->get($bill->id, 0)) > 0
                        || (float) ($chequePerBill->get($bill->id, 0)) > 0;
                })->values();
            }

            $totalDue = $bills->sum(fn($b) => (float) $duePerBill->get($b->id, 0));
            $totalDueBills = $bills->filter(fn($b) => (float) $duePerBill->get($b->id, 0) > 0)->count();
            $totalCheque = $bills->sum(fn($b) => (float) $chequePerBill->get($b->id, 0));
            $totalChequeBills = $bills->filter(fn($b) => (float) $chequePerBill->get($b->id, 0) > 0)->count();
            $totalBills = $bills->count();
            $totalBillAmount = $bills->sum(fn($b) => (float) $b->bill_amount);

            return view('reports.dues', compact(
                'detailMode', 'detailDate', 'bills', 'duePerBill', 'chequePerBill',
                'users', 'type', 'totalBills', 'totalBillAmount',
                'totalDue', 'totalDueBills', 'totalCheque', 'totalChequeBills', 'dateFrom', 'dateTo'
            ));
        }

        $billsQuery = Bill::query();
        $applyScope($billsQuery);
        $billsPerDate = (clone $billsQuery)
            ->selectRaw('DATE(report_date) as bill_date')
            ->selectRaw('COUNT(id) as bill_count')
            ->groupBy('bill_date')
            ->get()
            ->keyBy('bill_date');

        $duesQuery = Due::query()->join('bills', 'bills.id', '=', 'dues.bill_id');
        $applyScope($duesQuery);
        $duesPerDate = (clone $duesQuery)
            ->selectRaw('DATE(bills.report_date) as bill_date')
            ->selectRaw('COUNT(dues.id) as due_bills')
            ->selectRaw('COALESCE(SUM(dues.original_amount), 0) as due_total')
            ->groupBy('bill_date')
            ->get()
            ->keyBy('bill_date');

        $chequeQuery = Payment::query()
            ->join('bills', 'bills.id', '=', 'payments.bill_id')
            ->where('payments.payment_type', 'check');
        $applyScope($chequeQuery);
        $chequesPerDate = (clone $chequeQuery)
            ->selectRaw('DATE(bills.report_date) as bill_date')
            ->selectRaw('COUNT(payments.id) as cheque_bills')
            ->selectRaw('COALESCE(SUM(payments.check_amount), 0) as cheque_total')
            ->groupBy('bill_date')
            ->get()
            ->keyBy('bill_date');

        $dates = $billsPerDate->keys()
            ->merge($duesPerDate->keys())
            ->merge($chequesPerDate->keys())
            ->unique()
            ->sortByDesc(fn($d) => $d);

        $rows = collect();
        foreach ($dates as $date) {
            $rows->push((object) [
                'bill_date' => $date,
                'bill_count' => (int) ($billsPerDate[$date]->bill_count ?? 0),
                'due_bills' => (int) ($duesPerDate[$date]->due_bills ?? 0),
                'due_total' => (float) ($duesPerDate[$date]->due_total ?? 0),
                'cheque_bills' => (int) ($chequesPerDate[$date]->cheque_bills ?? 0),
                'cheque_total' => (float) ($chequesPerDate[$date]->cheque_total ?? 0),
            ]);
        }

        $totalBills = $rows->sum('bill_count');
        $totalDue = $rows->sum('due_total');
        $totalDueBills = $rows->sum('due_bills');
        $totalCheque = $rows->sum('cheque_total');
        $totalChequeBills = $rows->sum('cheque_bills');

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $rows = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $detailMode = false;

        return view('reports.dues', compact(
            'detailMode', 'rows', 'users', 'type',
            'totalBills', 'totalDue', 'totalDueBills', 'totalCheque', 'totalChequeBills', 'dateFrom', 'dateTo'
        ));
    }

    public function inactiveCustomers(Request $request): View
    {
        $query = Customer::with('creator')->where('is_active', false);

        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('id', 'desc')->paginate(20);
        $customers->appends($request->only('search'));

        return view('reports.inactive-customers', compact('customers'));
    }

    public function analytics(Request $request): View
    {
        $period = $request->get('period', '30');
        $days = (int) $period;
        $startDate = now()->subDays($days);
        $endDate = now();

        $baseBillQuery = Bill::query();
        $baseDueQuery = Due::query();
        $basePaymentQuery = \App\Models\Payment::query();

        if (!Auth::user()->isAdmin()) {
            $baseBillQuery->where('user_id', Auth::id());
            $baseDueQuery->where('created_by', Auth::id());
            $basePaymentQuery->whereHas('bill', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $periodBills = (clone $baseBillQuery)->whereBetween('report_date', [$startDate, $endDate])->get();
        $totalSales = $periodBills->sum('bill_amount');
        $totalDiscount = $periodBills->sum('discount');
        $totalReportDiscount = Auth::user()->isAdmin()
            ? TodaySalesReport::where('status', 'closed')
                ->whereBetween('report_date', [$startDate, $endDate])
                ->sum('discount_amt')
            : TodaySalesReport::where('user_id', Auth::id())
                ->where('status', 'closed')
                ->whereBetween('report_date', [$startDate, $endDate])
                ->sum('discount_amt');
        $netSales = $totalSales - $totalDiscount - $totalReportDiscount;
        $billCount = $periodBills->count();
        $avgBillValue = $billCount > 0 ? $netSales / $billCount : 0;

        $prevBills = (clone $baseBillQuery)
            ->whereBetween('report_date', [$startDate->copy()->subDays($days), $startDate])
            ->get();
        $prevSales = $prevBills->sum('bill_amount');
        $prevNetSales = $prevSales - $prevBills->sum('discount');
        $salesGrowth = $prevNetSales > 0 ? (($netSales - $prevNetSales) / $prevNetSales) * 100 : 0;

        $prevBillCount = $prevBills->count();
        $billCountGrowth = $prevBillCount > 0 ? (($billCount - $prevBillCount) / $prevBillCount) * 100 : 0;

        $avgPrevBill = $prevBillCount > 0 ? $prevNetSales / $prevBillCount : 0;
        $avgBillGrowth = $avgPrevBill > 0 ? (($avgBillValue - $avgPrevBill) / $avgPrevBill) * 100 : 0;

        $paymentBreakdown = (clone $basePaymentQuery)
            ->whereHas('bill', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('report_date', [$startDate, $endDate]);
            })
            ->selectRaw('payment_type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_type')
            ->get()
            ->keyBy('payment_type');

        $paymentTypes = [
            'cash' => ['total' => 0, 'count' => 0],
            'check' => ['total' => 0, 'count' => 0],
            'tt' => ['total' => 0, 'count' => 0],
            'card' => ['total' => 0, 'count' => 0],
            'due' => ['total' => 0, 'count' => 0],
        ];
        foreach ($paymentTypes as $type => $data) {
            if ($paymentBreakdown->has($type)) {
                $paymentTypes[$type] = [
                    'total' => (float) $paymentBreakdown[$type]->total,
                    'count' => (int) $paymentBreakdown[$type]->count,
                ];
            }
        }

        $dailySales = (clone $baseBillQuery)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->selectRaw('DATE(report_date) as date, SUM(bill_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyLabels = [];
        $dailyValues = [];
        $dailyCounts = [];
        $day = $startDate->copy();
        while ($day <= $endDate) {
            $dateStr = $day->format('Y-m-d');
            $dailyLabels[] = $day->format('M d');
            $match = $dailySales->firstWhere('date', $dateStr);
            $dailyValues[] = $match ? (float) $match->total : 0;
            $dailyCounts[] = $match ? (int) $match->count : 0;
            $day->addDay();
        }

        $topCustomers = (clone $baseBillQuery)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->with('customer')
            ->selectRaw('customer_id, SUM(bill_amount) as total, COUNT(*) as count')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $userPerformance = [];
        if (Auth::user()->isAdmin()) {
            $users = User::where('role', 'user')->get();
            foreach ($users as $user) {
                $userBills = Bill::where('user_id', $user->id)
                    ->whereBetween('report_date', [$startDate, $endDate])
                    ->get();
                $uReportDiscount = TodaySalesReport::where('user_id', $user->id)
                    ->where('status', 'closed')
                    ->whereBetween('report_date', [$startDate, $endDate])
                    ->sum('discount_amt');
                $userPerformance[] = [
                    'name' => $user->name,
                    'sales' => $userBills->sum('bill_amount'),
                    'bills' => $userBills->count(),
                    'discount' => $userBills->sum('discount'),
                    'report_discount' => $uReportDiscount,
                ];
            }
            usort($userPerformance, function ($a, $b) {
                return $b['sales'] <=> $a['sales'];
            });
        }

        $dueStats = [
            'total_pending' => (clone $baseDueQuery)->where('status', 'pending')->sum('amount'),
            'total_partial' => (clone $baseDueQuery)->where('status', 'pending')->whereHas('duePayments')->sum('amount'),
            'total_paid' => (clone $baseDueQuery)->where('status', 'paid')->sum('original_amount'),
            'pending_count' => (clone $baseDueQuery)->where('status', 'pending')->count(),
            'paid_count' => (clone $baseDueQuery)->where('status', 'paid')->count(),
            'partial_count' => (clone $baseDueQuery)->where('status', 'pending')->whereHas('duePayments')->count(),
        ];

        $overdueDues = (clone $baseDueQuery)
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount');

        $checkPending = (clone $basePaymentQuery)
            ->where('payment_type', 'check')
            ->where('status', 'pending')
            ->sum('check_amount');

        $collectionRate = $totalSales > 0 ? ($paymentTypes['cash']['total'] + $paymentTypes['check']['total'] + $paymentTypes['tt']['total'] + $paymentTypes['card']['total']) / $totalSales * 100 : 0;

        $dueCollection = DuePayment::whereHas('due', function ($q) use ($startDate, $endDate, $baseDueQuery) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
                if (!Auth::user()->isAdmin()) {
                    $q->where('created_by', Auth::id());
                }
            })
            ->sum('amount');

        $recoveryRate = $dueStats['total_paid'] > 0 ? ($dueStats['total_paid'] / ($dueStats['total_paid'] + $dueStats['total_pending'])) * 100 : 0;

        return view('reports.analytics', compact(
            'period',
            'totalSales',
            'totalDiscount',
            'totalReportDiscount',
            'netSales',
            'billCount',
            'avgBillValue',
            'salesGrowth',
            'billCountGrowth',
            'avgBillGrowth',
            'paymentTypes',
            'dailyLabels',
            'dailyValues',
            'dailyCounts',
            'topCustomers',
            'userPerformance',
            'dueStats',
            'overdueDues',
            'checkPending',
            'collectionRate',
            'dueCollection',
            'recoveryRate',
        ));
    }

    public function resources(Request $request): View
    {
        $users = User::where('role', 'user')->orderBy('name')->get(['id', 'name']);

        $query = Bill::with(['customer', 'user', 'payments']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        $query->orderBy('report_date', 'desc')->orderBy('id', 'desc');
        $bills = $query->paginate(25);

        // Summary stats for the filtered result
        $summaryQuery = Bill::query()
            ->when(Auth::user()->isAdmin() && $request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('user_id', Auth::id()))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('report_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('report_date', '<=', $request->date_to));

        $totalBills = (clone $summaryQuery)->count();
        $grossAmount = (clone $summaryQuery)->sum('bill_amount');
        $totalDiscount = (clone $summaryQuery)->sum('discount');

        $billIds = (clone $summaryQuery)->pluck('id');

        $paymentTotals = Payment::whereIn('bill_id', $billIds)
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'cash' AND status = 'encashed' THEN amount ELSE 0 END), 0) as cash_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'check' THEN amount ELSE 0 END), 0) as check_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'tt' THEN amount ELSE 0 END), 0) as tt_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'card' THEN amount ELSE 0 END), 0) as card_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_type = 'due' THEN amount ELSE 0 END), 0) as due_total")
            ->first();

        $chequeEncashed = Payment::whereIn('bill_id', $billIds)
            ->where('payment_type', 'check')
            ->sum('encashed_amount');

        $chequeDiscount = CheckEncashment::whereHas('payment', function ($q) use ($billIds) {
            $q->whereIn('bill_id', $billIds)->where('payment_type', 'check');
        })->sum('discount');

        $dueCollection = DuePayment::whereHas('due.bill', function ($q) use ($billIds) {
            $q->whereIn('id', $billIds);
        })->sum('amount');

        $duePayDiscountQ = DuePayment::query();
        if (Schema::hasColumn('due_payments', 'status')) {
            $duePayDiscountQ->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'));
        } else {
            $duePayDiscountQ->where('payment_type', '!=', 'check');
        }
        $duePayDiscount = $duePayDiscountQ
            ->whereHas('due.bill', function ($q) use ($billIds) {
                $q->whereIn('id', $billIds);
            })->sum('discount');

        $duePendingAmount = Due::whereIn('bill_id', $billIds)
            ->where('status', 'pending')
            ->sum('amount');

        $prevDuePayQ = PreviousDuePayment::query();
        if (Schema::hasColumn('previous_due_payments', 'status')) {
            $prevDuePayQ->where(fn($q) => $q->where('payment_type', '!=', 'check')->orWhere('status', 'encashed'));
        } else {
            $prevDuePayQ->where('payment_type', '!=', 'check');
        }
        $prevDuePayDiscount = $prevDuePayQ
            ->whereHas('previousDue', function ($q) use ($request) {
                if ($request->filled('user_id')) {
                    $q->where('created_by', $request->user_id);
                } elseif (!Auth::user()->isAdmin()) {
                    $q->where('created_by', Auth::id());
                }
            })->sum('discount');

        $repDiscount = TodaySalesReport::where('status', 'closed')
            ->when(Auth::user()->isAdmin() && $request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('user_id', Auth::id()))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('report_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('report_date', '<=', $request->date_to))
            ->sum('discount_amt');

        return view('reports.resources', compact(
            'users', 'bills', 'totalBills', 'grossAmount', 'totalDiscount',
            'paymentTotals', 'chequeEncashed', 'chequeDiscount', 'dueCollection',
            'duePayDiscount', 'prevDuePayDiscount', 'duePendingAmount', 'repDiscount'
        ));
    }

    public function previousDue(Request $request): View
    {
        $query = PreviousDue::with(['customer', 'creator', 'payments']);

        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'partial') {
                $query->where('status', 'pending')->whereHas('payments');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'original_amount', 'amount', 'status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $previousDues = $query->paginate(20);
        $previousDues->appends($request->only('user_id', 'status', 'date_from', 'date_to', 'search', 'sort', 'direction'));

        $users = User::where('role', 'user')->get(['id', 'name']);

        $totalAmount = $previousDues->sum('original_amount');
        $totalPending = collect($previousDues->items())->sum(fn($pd) => $pd->remaining_amount);

        return view('reports.previous-dues', compact('previousDues', 'users', 'totalAmount', 'totalPending'));
    }
}