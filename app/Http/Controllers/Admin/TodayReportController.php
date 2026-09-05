<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Due;
use App\Models\Payment;
use App\Models\TodaySalesReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodayReportController extends Controller
{
    public function index(Request $request): View
    {
        $today = $request->input('date', now()->subDay()->toDateString());

        $users = User::where('role', 'user')->get();

        $userReports = $users->map(function ($user) use ($today) {
            $todayBills = Bill::with(['customer', 'payments', 'dues'])
                ->where('user_id', $user->id)
                ->whereDate('report_date', $today)
                ->get();

            $totalBills = $todayBills->count();
            $grossAmount = $todayBills->sum('bill_amount');
            $billDiscount = $todayBills->sum('discount');

            $allPayments = Payment::whereIn('bill_id', $todayBills->pluck('id'))->get();
            $cashAmt = $allPayments->where('payment_type', 'cash')->sum('amount');
            $chequeAmt = $allPayments->where('payment_type', 'check')->sum('amount');
            $ttAmt = $allPayments->where('payment_type', 'tt')->sum('amount');
            $refCardAmt = $allPayments->where('payment_type', 'card')->sum('amount');
            $totalReceived = $allPayments->sum('amount');

            $dueAmt = Due::whereIn('bill_id', $todayBills->pluck('id'))
                ->where('status', 'pending')
                ->sum('original_amount');

            $closedReport = TodaySalesReport::where('user_id', $user->id)
                ->where('status', 'closed')
                ->latest('report_date')
                ->first();

            return [
                'user' => $user,
                'total_bills' => $totalBills,
                'gross_amount' => $grossAmount,
                'bill_discount' => $billDiscount,
                'cash_amount' => $cashAmt,
                'cheque_amount' => $chequeAmt,
                'tt_amount' => $ttAmt,
                'ref_card_amount' => $refCardAmt,
                'total_received' => $totalReceived,
                'due_amount' => $dueAmt,
                'net_amount' => $grossAmount - $billDiscount,
                'is_closed' => $closedReport && $closedReport->report_date->toDateString() === $today,
                'closed_report' => $closedReport,
            ];
        });

        $totals = [
            'total_bills' => $userReports->sum('total_bills'),
            'gross_amount' => $userReports->sum('gross_amount'),
            'bill_discount' => $userReports->sum('bill_discount'),
            'total_received' => $userReports->sum('total_received'),
            'cash_amount' => $userReports->sum('cash_amount'),
            'cheque_amount' => $userReports->sum('cheque_amount'),
            'tt_amount' => $userReports->sum('tt_amount'),
            'ref_card_amount' => $userReports->sum('ref_card_amount'),
            'due_amount' => $userReports->sum('due_amount'),
            'net_amount' => $userReports->sum('net_amount'),
        ];

        return view('admin.today-report', compact('userReports', 'totals', 'today'));
    }

    public function userBills(Request $request, User $user): View
    {
        $date = $request->input('date', now()->subDay()->toDateString());

        // Drill-down detail view: every bill for this user on the selected date,
        // intentionally fetched in full (no pagination) per business requirement.
        $bills = Bill::with(['customer', 'payments', 'dues'])
            ->where('user_id', $user->id)
            ->whereDate('report_date', $date)
            ->orderBy('id', 'desc')
            ->get();

        $totalBills = $bills->count();
        $grossAmount = $bills->sum('bill_amount');
        $billDiscount = $bills->sum('discount');
        $netAmount = $grossAmount - $billDiscount;

        $allPayments = Payment::whereIn('bill_id', $bills->pluck('id'))->get();
        $cashAmt = $allPayments->where('payment_type', 'cash')->sum('amount');
        $chequeAmt = $allPayments->where('payment_type', 'check')->sum('amount');
        $ttAmt = $allPayments->where('payment_type', 'tt')->sum('amount');
        $refCardAmt = $allPayments->where('payment_type', 'card')->sum('amount');
        $totalReceived = $allPayments->sum('amount');

        $dueAmt = Due::whereIn('bill_id', $bills->pluck('id'))
            ->where('status', 'pending')
            ->sum('original_amount');

        $closedReport = TodaySalesReport::where('user_id', $user->id)
            ->where('report_date', $date)
            ->where('status', 'closed')
            ->first();

        return view('admin.today-report-user-bills', compact(
            'user',
            'date',
            'bills',
            'totalBills',
            'grossAmount',
            'billDiscount',
            'netAmount',
            'cashAmt',
            'chequeAmt',
            'ttAmt',
            'refCardAmt',
            'totalReceived',
            'dueAmt',
            'closedReport'
        ));
    }
}
