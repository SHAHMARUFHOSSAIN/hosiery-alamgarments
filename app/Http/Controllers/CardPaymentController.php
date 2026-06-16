<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\Bill;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CardPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['bill.customer'])
            ->where('payment_type', 'card')
            ->where('status', 'pending');

        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $query->whereIn('bill_id', $billIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('card_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('card_date', '<=', $request->date_to);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['card_date', 'card_amount', 'created_at', 'card_reference', 'card_location'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $cardPayments = $query->paginate(20);
        $cardPayments->appends($request->only('search', 'date_from', 'date_to', 'sort', 'direction'));

        $totalPending = Payment::where('payment_type', 'card')
            ->where('status', 'pending')
            ->sum('amount');

        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $totalPending = Payment::whereIn('bill_id', $billIds)
                ->where('payment_type', 'card')
                ->where('status', 'pending')
                ->sum('amount');
        }

        return view('card-payments.index', compact('cardPayments', 'totalPending'));
    }

    public function encash($id): \Illuminate\Http\RedirectResponse
    {
        $payment = Payment::with(['bill.customer'])->find($id);

        if (!$payment || $payment->payment_type !== 'card' || $payment->status !== 'pending') {
            return redirect()->route('card-payments.index')
                ->with('error', 'Card payment not found or already encashed');
        }

        $payment->update(['status' => 'encashed']);

        $due = Due::where('bill_id', $payment->bill_id)->first();
        if ($due) {
            $netAmount = ($payment->bill->bill_amount ?? 0) - ($payment->bill->discount ?? 0);
            if ((float) $due->original_amount === (float) $netAmount) {
                $newAmount = max(0, $due->amount - $payment->amount);
                $due->update([
                    'amount' => $newAmount,
                    'status' => $newAmount <= 0 ? 'paid' : $due->status,
                ]);
            }
        }

        $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => 'Card Collection - ' . ($payment->bill->customer->name ?? 'Customer'),
            'amount' => $payment->amount,
            'balance' => $lastBal + $payment->amount,
            'type' => 'credit',
            'note' => 'Card payment collected (Bill: ' . ($payment->bill->bill_no ?? 'N/A') . ')',
            'user_id' => Auth::id(),
            'branch_id' => Auth::id(),
        ]);

        return redirect()->route('card-payments.index')
            ->with('success', 'Card payment encashed successfully');
    }
}
