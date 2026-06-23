<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Due;
use App\Models\Payment;
use App\Models\TodaySalesReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TodaySalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $reportDate = $request->get('date', now()->toDateString());
        $userId = Auth::id();

        $todayBills = Bill::with(['customer', 'payments', 'dues'])
            ->where('user_id', $userId)
            ->whereDate('report_date', $reportDate)
            ->orderBy('id', 'desc')
            ->get();

        $totalBills = $todayBills->count();
        $grossAmount = $todayBills->sum('bill_amount');
        $billDiscount = $todayBills->sum('discount');

        $allPayments = Payment::whereIn('bill_id', $todayBills->pluck('id'))->get();
        $chequeAmt = $allPayments->where('payment_type', 'check')->sum('amount');
        $refCardAmt = $allPayments->where('payment_type', 'card')->sum('amount');
        $cashAmt = $allPayments->where('payment_type', 'cash')->sum('amount');
        $ttAmt = $allPayments->where('payment_type', 'tt')->sum('amount');
        $totalReceived = $allPayments->sum('amount');

        $dueAmt = Due::whereIn('bill_id', $todayBills->pluck('id'))
            ->where('status', 'pending')
            ->sum('amount');

        $existingReport = TodaySalesReport::where('report_date', $reportDate)
            ->where('user_id', $userId)
            ->first();

        return view('user-reports.today-sales', compact(
            'reportDate',
            'todayBills',
            'totalBills',
            'grossAmount',
            'billDiscount',
            'chequeAmt',
            'refCardAmt',
            'cashAmt',
            'ttAmt',
            'totalReceived',
            'dueAmt',
            'existingReport'
        ));
    }

    public function close(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'discount_amt' => 'required|numeric|min:0',
            'report_date' => 'required|date',
        ]);

        $reportDate = $validated['report_date'];
        $userId = Auth::id();

        $existing = TodaySalesReport::where('report_date', $reportDate)
            ->where('user_id', $userId)
            ->where('status', 'closed')
            ->first();

        if ($existing) {
            return redirect()->route('user-reports.today-sales', ['date' => $reportDate])
                ->with('error', 'Report for this date is already closed.');
        }

        $todayBills = Bill::where('user_id', $userId)
            ->whereDate('report_date', $reportDate)
            ->get();

        $totalBills = $todayBills->count();
        $grossAmount = $todayBills->sum('bill_amount');

        $billIds = $todayBills->pluck('id');
        $allPayments = Payment::whereIn('bill_id', $billIds)->get();
        $chequeAmt = $allPayments->where('payment_type', 'check')->sum('amount');
        $refCardAmt = $allPayments->where('payment_type', 'card')->sum('amount');
        $dueAmt = Due::whereIn('bill_id', $billIds)
            ->where('status', 'pending')
            ->sum('amount');

        $billDiscountAmt = $todayBills->sum('discount');
        $discountAmt = $validated['discount_amt'];
        $finalAmount = $grossAmount - $billDiscountAmt - $discountAmt;

        TodaySalesReport::updateOrCreate(
            ['report_date' => $reportDate, 'user_id' => $userId],
            [
                'total_bills' => $totalBills,
                'gross_amount' => $grossAmount,
                'cheque_amt' => $chequeAmt,
                'ref_card_amt' => $refCardAmt,
                'discount_amt' => $discountAmt,
                'due_amt' => $dueAmt,
                'final_amount' => max(0, $finalAmount),
                'status' => 'closed',
                'closed_by' => Auth::id(),
                'closed_at' => now(),
            ]
        );

        return redirect()->route('user-reports.today-sales', ['date' => $reportDate])
            ->with('success', 'Sales report closed successfully.');
    }
}
