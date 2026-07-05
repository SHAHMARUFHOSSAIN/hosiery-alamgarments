<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\Bill;
use App\Models\Due;
use App\Models\DuePayment;
use App\Models\MainBalance;
use App\Models\CheckEncashment;
use App\Models\Payment;
use App\Models\PreviousDuePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DueController extends Controller
{
    public function index(Request $request): View
    {
        $query = Due::with(['customer', 'bill', 'duePayments.user']);
        
        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'partial') {
                $query->where('status', 'pending')
                      ->whereHas('duePayments');
            } else {
                $query->where('status', $request->status);
            }
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('mobile', 'like', "%{$search}%");
                })->orWhereHas('bill', function ($bq) use ($search) {
                    $bq->where('bill_no', 'like', "%{$search}%");
                });
            });
        }
        
        $sortField = $request->get('sort', 'due_date');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['id', 'original_amount', 'due_date', 'status', 'remaining_amount'];
        
        if ($sortField === 'remaining_amount') {
            $query->orderBy('amount', $sortDirection);
        } elseif (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                  ->orderBy('due_date', 'asc');
        }
        
        $totalPendingAmount = (clone $query)->whereIn('status', ['pending'])->get()->sum(function ($d) {
            return $d->remaining_amount > 0 ? $d->remaining_amount : $d->original_amount;
        });

        $dues = $query->paginate(20);

        return view('dues.index', compact('dues', 'totalPendingAmount'));
    }

    public function dailyReport(): View
    {
        $query = Due::with(['customer', 'creator', 'duePayments.user', 'bill'])
            ->whereDate('due_date', now()->toDateString())
            ->where('status', 'pending');
            
        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }
        
        $allDues = (clone $query)->with('duePayments')->get();
        $totalAmount = $allDues->sum(fn($due) => $due->remaining_amount);
        $todayDues = $query->orderBy('due_date', 'asc')->paginate(20);
        return view('dues.daily-report', compact('todayDues', 'totalAmount'));
    }

    public function checksReport(Request $request): View
    {
        $query = Payment::with(['bill.customer', 'bill.user', 'checkEncashments.user'])
            ->where('payment_type', 'check');
            
        if ($request->filled('status')) {
            if ($request->status === 'partial') {
                $query->where('partially_encashed', true);
            } else {
                $query->where('status', $request->status);
            }
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('bank')) {
            $query->where('bank_name', 'like', "%{$request->bank}%");
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('check_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('check_date', '<=', $request->date_to);
        }
        
        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $query->whereIn('bill_id', $billIds);
        }
        
        $sortField = $request->get('sort', 'check_date');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['bank_name', 'check_no', 'check_amount', 'encashed_amount', 'check_date', 'status'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('check_date', 'asc');
        }
        
        $allChecks = $query->paginate(20);
        $allChecks->appends($request->only('status', 'search', 'bank', 'date_from', 'date_to', 'sort', 'direction'));

        $allChecksQuery = Payment::with(['bill.customer', 'bill.user', 'checkEncashments.user'])
            ->where('payment_type', 'check');

        if ($request->filled('status')) {
            if ($request->status === 'partial') {
                $allChecksQuery->where('partially_encashed', true);
            } else {
                $allChecksQuery->where('status', $request->status);
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $allChecksQuery->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }
        if ($request->filled('bank')) {
            $allChecksQuery->where('bank_name', 'like', "%{$request->bank}%");
        }
        if ($request->filled('date_from')) {
            $allChecksQuery->whereDate('check_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $allChecksQuery->whereDate('check_date', '<=', $request->date_to);
        }
        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $allChecksQuery->whereIn('bill_id', $billIds);
        }

        $allChecksResult = $allChecksQuery->get();
        $totalCheckAmount = $allChecksResult->sum('check_amount');
        $totalEncashedAmount = $allChecksResult->sum('encashed_amount');
        $totalRemainingAmount = $allChecksResult->sum(fn($p) => $p->remainingCheckAmount());

        $banks = Payment::where('payment_type', 'check')
            ->whereNotNull('bank_name')
            ->distinct()
            ->pluck('bank_name');

        $dueCheckQuery = DuePayment::with(['due.customer', 'due.bill', 'user'])
            ->where('payment_type', 'check');

        if ($request->filled('bank')) {
            $dueCheckQuery->where('bank_name', 'like', "%{$request->bank}%");
        }
        if ($request->filled('date_from')) {
            $dueCheckQuery->whereDate('check_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $dueCheckQuery->whereDate('check_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $dueCheckQuery->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('check_no', 'like', "%{$search}%")
                  ->orWhereHas('due.customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if (!Auth::user()->isAdmin()) {
            $dueCheckQuery->whereHas('due', fn($q) => $q->where('created_by', Auth::id()));
        }

        $dueChecks = $dueCheckQuery->orderBy('check_date', 'asc')->paginate(20, ['*'], 'due_page');

        $prevDueCheckQuery = PreviousDuePayment::with(['previousDue.customer', 'user'])
            ->where('payment_type', 'check');

        if ($request->filled('bank')) {
            $prevDueCheckQuery->where('bank_name', 'like', "%{$request->bank}%");
        }
        if ($request->filled('date_from')) {
            $prevDueCheckQuery->whereDate('check_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $prevDueCheckQuery->whereDate('check_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $prevDueCheckQuery->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('check_no', 'like', "%{$search}%")
                  ->orWhereHas('previousDue.customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if (!Auth::user()->isAdmin()) {
            $prevDueCheckQuery->whereHas('previousDue', fn($q) => $q->where('created_by', Auth::id()));
        }

        $prevDueChecks = $prevDueCheckQuery->orderBy('check_date', 'asc')->paginate(20, ['*'], 'prev_due_page');

        return view('dues.checks-report', compact(
            'allChecks', 'banks', 'totalCheckAmount', 'totalEncashedAmount', 'totalRemainingAmount',
            'dueChecks', 'prevDueChecks'
        ));
    }

    public function ttReport(Request $request): View
    {
        $query = Payment::with(['bill.customer', 'bill.user'])
            ->where('payment_type', 'tt');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bank')) {
            $query->where('tt_bank_name', 'like', "%{$request->bank}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('tt_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tt_date', '<=', $request->date_to);
        }

        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $query->whereIn('bill_id', $billIds);
        }

        $sortField = $request->get('sort', 'tt_date');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['tt_bank_name', 'tt_amount', 'tt_date', 'status'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('tt_date', 'asc');
        }

        $ttPayments = $query->paginate(20);
        $ttPayments->appends($request->only('status', 'search', 'bank', 'date_from', 'date_to', 'sort', 'direction'));

        $allTtQuery = Payment::with(['bill.customer', 'bill.user'])
            ->where('payment_type', 'tt');

        if ($request->filled('status')) {
            $allTtQuery->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $allTtQuery->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }
        if ($request->filled('bank')) {
            $allTtQuery->where('tt_bank_name', 'like', "%{$request->bank}%");
        }
        if ($request->filled('date_from')) {
            $allTtQuery->whereDate('tt_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $allTtQuery->whereDate('tt_date', '<=', $request->date_to);
        }
        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $allTtQuery->whereIn('bill_id', $billIds);
        }

        $allTtResult = $allTtQuery->get();
        $totalTtAmount = $allTtResult->sum('tt_amount');
        $totalPayments = $allTtResult->count();

        $banks = Payment::where('payment_type', 'tt')
            ->whereNotNull('tt_bank_name')
            ->distinct()
            ->pluck('tt_bank_name');

        return view('dues.tt-report', compact('ttPayments', 'banks', 'totalTtAmount', 'totalPayments'));
    }

    public function cashReport(Request $request): View
    {
        $query = Payment::with(['bill.customer', 'bill.user'])
            ->where('payment_type', 'cash');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $query->whereIn('bill_id', $billIds);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['amount', 'created_at', 'status'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $cashPayments = $query->paginate(20);
        $cashPayments->appends($request->only('status', 'search', 'date_from', 'date_to', 'sort', 'direction'));

        $allCashQuery = Payment::with(['bill.customer', 'bill.user'])
            ->where('payment_type', 'cash');

        if ($request->filled('status')) {
            $allCashQuery->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $allCashQuery->whereHas('bill', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $allCashQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $allCashQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if (!Auth::user()->isAdmin()) {
            $billIds = Bill::where('user_id', Auth::id())->pluck('id');
            $allCashQuery->whereIn('bill_id', $billIds);
        }

        $allCashResult = $allCashQuery->get();
        $totalCashAmount = $allCashResult->sum('amount');
        $totalPayments = $allCashResult->count();

        return view('dues.cash-report', compact('cashPayments', 'totalCashAmount', 'totalPayments'));
    }

    public function encashCheck(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'encash_amount' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,mobile_banking',
            'next_due_date' => 'nullable|date|after_or_equal:today',
            'note' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $payment = Payment::with(['bill.customer'])->find($id);

        if (!$payment || $payment->payment_type !== 'check' || $payment->status === 'encashed') {
            return redirect()->back()->with('error', 'Could not encash check');
        }

        $remainingCheck = $payment->remainingCheckAmount();
        $discount = (float) ($validated['discount'] ?? 0);

        if ($validated['encash_amount'] + $discount > $remainingCheck) {
            return redirect()->back()->with('error', 'Encash amount plus discount cannot exceed remaining check amount');
        }

        CheckEncashment::create([
            'payment_id' => $payment->id,
            'encash_amount' => $validated['encash_amount'],
            'discount' => $discount,
            'payment_type' => $validated['payment_type'],
            'encash_date' => now(),
            'next_due_date' => $validated['next_due_date'] ?? null,
            'note' => $validated['note'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $newEncashed = (float) $payment->encashed_amount + $validated['encash_amount'];
        $newRemaining = (float) $payment->check_amount - $newEncashed - $discount;

        $payment->update([
            'encashed_amount' => $newEncashed,
            'partially_encashed' => $newRemaining > 0,
            'status' => $newRemaining <= 0 ? 'encashed' : 'pending',
        ]);

        if ($validated['next_due_date'] ?? null) {
            $payment->update(['check_reminder_date' => $validated['next_due_date']]);
        }

        $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
        $mainBalanceNote = 'Cheque encashed via ' . $validated['payment_type'] . ': ' . ($payment->bank_name ?? 'N/A') . ' - ' . ($payment->check_no ?? 'N/A');
        if ($validated['transaction_id'] ?? null) {
            $mainBalanceNote .= ' | TxnID: ' . $validated['transaction_id'];
        }
        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => 'Cheque Encashed - Bill #' . ($payment->bill?->bill_no ?? 'N/A'),
            'amount' => $validated['encash_amount'],
            'balance' => $lastBal + $validated['encash_amount'],
            'type' => 'credit',
            'note' => $mainBalanceNote,
            'user_id' => Auth::id(),
            'branch_id' => Auth::id(),
        ]);

        if ($discount > 0) {
            $newBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Discount - ' . ($payment->bill?->customer?->name ?? 'Customer'),
                'amount' => $discount,
                'balance' => $newBal - $discount,
                'type' => 'debit',
                'note' => 'Discount on cheque encashment (Bill: ' . ($payment->bill?->bill_no ?? 'N/A') . ')',
                'user_id' => Auth::id(),
                'branch_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Cheque encashed successfully');
    }

    public function dueEncashCheck(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'due_payment_id' => 'required|exists:due_payments,id',
            'encash_amount' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,mobile_banking',
            'note' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $duePayment = DuePayment::with('due.customer')->findOrFail($validated['due_payment_id']);

        if ($duePayment->status === 'encashed') {
            return redirect()->back()->with('error', 'This cheque is already encashed');
        }

        $newDiscount = (float) ($validated['discount'] ?? 0);
        $existingDiscount = (float) $duePayment->discount;
        $totalDiscount = $existingDiscount + $newDiscount;
        $chequeRemaining = ($duePayment->check_amount ?? $duePayment->amount) - $duePayment->encashed_amount;
        if ($validated['encash_amount'] + $newDiscount > $chequeRemaining) {
            return redirect()->back()->with('error', 'Encash amount plus discount cannot exceed remaining cheque amount');
        }

        $newEncashed = (float) $duePayment->encashed_amount + $validated['encash_amount'];
        $duePayment->update([
            'discount' => $totalDiscount,
            'encashed_amount' => $newEncashed,
            'status' => 'encashed',
        ]);

        $due = $duePayment->due;
        $dueRemaining = $due->remaining_amount;
        $due->update([
            'amount' => $dueRemaining,
            'status' => $dueRemaining <= 0 ? 'paid' : 'pending',
        ]);

        $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
        $mainBalanceNote = 'Due cheque encashed: ' . ($duePayment->bank_name ?? 'N/A') . ' - ' . ($duePayment->check_no ?? 'N/A');
        if ($validated['transaction_id'] ?? null) {
            $mainBalanceNote .= ' | TxnID: ' . $validated['transaction_id'];
        }
        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => 'Due Cheque Encashed - ' . ($due->customer->name ?? 'Customer'),
            'amount' => $validated['encash_amount'],
            'balance' => $lastBal + $validated['encash_amount'],
            'type' => 'credit',
            'note' => $mainBalanceNote,
            'user_id' => Auth::id(),
            'branch_id' => Auth::id(),
        ]);

        if ($totalDiscount > 0) {
            $newBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Discount - ' . ($due->customer->name ?? 'Customer'),
                'amount' => $totalDiscount,
                'balance' => $newBal - $totalDiscount,
                'type' => 'debit',
                'note' => 'Discount on due cheque encashment',
                'user_id' => Auth::id(),
                'branch_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Due cheque encashed successfully');
    }

    public function prevDueEncashCheck(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'prev_due_payment_id' => 'required|exists:previous_due_payments,id',
            'encash_amount' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,mobile_banking',
            'note' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $prevDuePayment = PreviousDuePayment::with('previousDue.customer')->findOrFail($validated['prev_due_payment_id']);

        if ($prevDuePayment->status === 'encashed') {
            return redirect()->back()->with('error', 'This cheque is already encashed');
        }

        $newDiscount = (float) ($validated['discount'] ?? 0);
        $existingDiscount = (float) $prevDuePayment->discount;
        $totalDiscount = $existingDiscount + $newDiscount;
        $chequeRemaining = ($prevDuePayment->check_amount ?? $prevDuePayment->amount) - $prevDuePayment->encashed_amount;
        if ($validated['encash_amount'] + $newDiscount > $chequeRemaining) {
            return redirect()->back()->with('error', 'Encash amount plus discount cannot exceed remaining cheque amount');
        }

        $newEncashed = (float) $prevDuePayment->encashed_amount + $validated['encash_amount'];
        $prevDuePayment->update([
            'discount' => $totalDiscount,
            'encashed_amount' => $newEncashed,
            'status' => 'encashed',
        ]);

        $prevDue = $prevDuePayment->previousDue;
        $prevDueRemaining = $prevDue->remaining_amount;
        $prevDue->update([
            'amount' => $prevDueRemaining,
            'status' => $prevDueRemaining <= 0 ? 'paid' : 'pending',
        ]);

        $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
        $mainBalanceNote = 'Prev due cheque encashed: ' . ($prevDuePayment->bank_name ?? 'N/A') . ' - ' . ($prevDuePayment->check_no ?? 'N/A');
        if ($validated['transaction_id'] ?? null) {
            $mainBalanceNote .= ' | TxnID: ' . $validated['transaction_id'];
        }
        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => 'Prev Due Cheque Encashed - ' . ($prevDue->customer->name ?? 'Customer'),
            'amount' => $validated['encash_amount'],
            'balance' => $lastBal + $validated['encash_amount'],
            'type' => 'credit',
            'note' => $mainBalanceNote,
            'user_id' => Auth::id(),
            'branch_id' => Auth::id(),
        ]);

        if ($totalDiscount > 0) {
            $newBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Discount - ' . ($prevDue->customer->name ?? 'Customer'),
                'amount' => $totalDiscount,
                'balance' => $newBal - $totalDiscount,
                'type' => 'debit',
                'note' => 'Discount on prev due cheque encashment',
                'user_id' => Auth::id(),
                'branch_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Previous due cheque encashed successfully');
    }

    public function addPayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'due_id' => 'required|exists:dues,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,mobile_banking',
            'next_due_date' => 'nullable|date|after_or_equal:today',
            'note' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:100',
        ];

        if ($request->payment_type === 'check') {
            $rules = array_merge($rules, [
                'bank_name' => 'required|string|max:255',
                'check_no' => 'required|string|max:255',
                'check_date' => 'required|date',
                'check_amount' => 'required|numeric|min:0.01',
                'check_reminder_date' => 'nullable|date',
                'check_photo' => 'nullable|image|max:5120',
            ]);
        }

        $validated = $request->validate($rules);

        $due = Due::with('customer')->findOrFail($validated['due_id']);
        $remaining = $due->remaining_amount;
        $discount = (float) ($validated['discount'] ?? 0);

        if ($validated['payment_amount'] + $discount > $remaining) {
            return redirect()->back()->with('error', 'Payment amount plus discount cannot exceed remaining due amount');
        }

        $newRemaining = $remaining - $validated['payment_amount'] - $discount;

        $payData = [
            'due_id' => $due->id,
            'amount' => $validated['payment_amount'],
            'discount' => $discount,
            'payment_type' => $validated['payment_type'],
            'payment_date' => now(),
            'remaining_amount' => $request->payment_type === 'check' ? $remaining : $newRemaining,
            'note' => $validated['note'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'user_id' => Auth::id(),
        ];

        if ($request->payment_type === 'check') {
            $checkPhotoPath = null;
            if ($request->hasFile('check_photo')) {
                $checkPhotoPath = $request->file('check_photo')->store('cheque', 'public');
            }
            $payData = array_merge($payData, [
                'bank_name' => $validated['bank_name'],
                'check_no' => $validated['check_no'],
                'check_date' => $validated['check_date'],
                'check_amount' => $validated['check_amount'],
                'check_reminder_date' => $validated['check_reminder_date'] ?? null,
                'check_photo' => $checkPhotoPath,
                'encashed_amount' => 0,
                'status' => 'pending',
            ]);
        }

        DuePayment::create($payData);

        if ($request->payment_type !== 'check') {
            $due->update([
                'amount' => $newRemaining,
                'status' => $newRemaining <= 0 ? 'paid' : 'pending',
            ]);

            if ($validated['next_due_date']) {
                $due->update(['due_date' => $validated['next_due_date']]);
            }

            $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
            $mainBalanceNote = 'Payment via ' . $validated['payment_type'] . ' (Bill: ' . ($due->bill?->bill_no ?? 'N/A') . ')';
            if ($validated['transaction_id'] ?? null) {
                $mainBalanceNote .= ' | TxnID: ' . $validated['transaction_id'];
            }
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Due Payment - ' . ($due->customer?->name ?? 'Customer'),
                'amount' => $validated['payment_amount'],
                'balance' => $lastBal + $validated['payment_amount'],
                'type' => 'credit',
                'note' => $mainBalanceNote,
                'user_id' => Auth::id(),
                'branch_id' => Auth::id(),
            ]);

            if ($discount > 0) {
                $newBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
                MainBalance::create([
                    'voucher_no' => VoucherHelper::generateVoucherNo(),
                    'name' => 'Discount - ' . ($due->customer?->name ?? 'Customer'),
                    'amount' => $discount,
                    'balance' => $newBal - $discount,
                    'type' => 'debit',
                    'note' => 'Discount on due payment (Bill: ' . ($due->bill?->bill_no ?? 'N/A') . ')',
                    'user_id' => Auth::id(),
                    'branch_id' => Auth::id(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Payment recorded successfully');
    }

    public function markPaid($id): \Illuminate\Http\RedirectResponse
    {
        $due = Due::with('customer')->find($id);
        if ($due) {
            DuePayment::create([
                'due_id' => $due->id,
                'amount' => $due->amount,
                'payment_type' => 'cash',
                'payment_date' => now(),
                'remaining_amount' => 0,
                'note' => 'Full payment',
                'user_id' => Auth::id(),
            ]);

            $due->update(['status' => 'paid', 'amount' => 0]);

            $lastBal = MainBalance::where('branch_id', $due->created_by)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Due Collection - ' . ($due->customer->name ?? 'Customer'),
                'amount' => $due->original_amount,
                'balance' => $lastBal + $due->original_amount,
                'type' => 'credit',
                'note' => 'Due collected',
                'user_id' => Auth::id(),
                'branch_id' => $due->created_by,
            ]);

            return redirect()->back()->with('success', 'Marked as paid');
        }
        return redirect()->back()->with('error', 'Due not found');
    }
}