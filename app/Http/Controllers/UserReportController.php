<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserReportController extends Controller
{
    public function index(): View
    {
        $totalSales = Bill::where('user_id', Auth::id())->sum('bill_amount');
        
        $totalDue = Due::where('created_by', Auth::id())->where('status', 'pending')->sum('amount');
        $paidDue = Due::where('created_by', Auth::id())->where('status', 'paid')->sum('amount');
        
        $totalCredit = MainBalance::where('branch_id', Auth::id())->where('type', 'credit')->sum('amount');
        $totalDebit = MainBalance::where('branch_id', Auth::id())->where('type', 'debit')->sum('amount');
        $mainBalance = $totalCredit - $totalDebit;

        return view('user-reports.index', compact('totalSales', 'totalDue', 'paidDue', 'mainBalance'));
    }

    public function sales(Request $request): View
    {
        $query = Bill::with('customer')->where('user_id', Auth::id());

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $todayQuery = Bill::with('customer')->where('user_id', Auth::id())
            ->whereDate('created_at', now()->toDateString());

        $bills = $query->orderBy('id', 'desc')->paginate(15);
        $totalAmount = $query->sum('bill_amount');
        $totalDiscount = $query->sum('discount');
        
        $dailyAmount = $todayQuery->sum('bill_amount');
        $dailyDiscount = $todayQuery->sum('discount');

        return view('user-reports.sales', compact('bills', 'totalAmount', 'totalDiscount', 'dailyAmount', 'dailyDiscount'));
    }

    public function dues(Request $request): View
    {
        $query = Due::with('customer', 'bill')->where('created_by', Auth::id());

        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dues = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                    ->orderBy('due_date', 'asc')
                    ->paginate(15);
        $totalPending = $query->clone()->where('status', 'pending')->sum('amount');
        $totalPaid = $query->clone()->where('status', 'paid')->sum('amount');
        
        $totalCredit = MainBalance::where('branch_id', Auth::id())->where('type', 'credit')->sum('amount');
        $totalDebit = MainBalance::where('branch_id', Auth::id())->where('type', 'debit')->sum('amount');
        $mainBalance = $totalCredit - $totalDebit;

        return view('user-reports.dues', compact('dues', 'totalPending', 'totalPaid', 'mainBalance'));
    }
}