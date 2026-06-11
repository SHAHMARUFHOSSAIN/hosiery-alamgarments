<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\Bill;
use App\Models\Due;
use App\Models\DuePayment;
use App\Models\MainBalance;
use App\Models\Payment;
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
        
        $dues = $query->get();
        
        return view('dues.index', compact('dues'));
    }

    public function dailyReport(): View
    {
        $query = Due::with(['customer', 'creator', 'duePayments.user'])
            ->whereDate('due_date', now()->toDateString())
            ->where('status', 'pending');
            
        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }
        
        $todayDues = $query->orderBy('due_date', 'asc')->get();
        return view('dues.daily-report', compact('todayDues'));
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
        
        $banks = Payment::where('payment_type', 'check')
            ->whereNotNull('bank_name')
            ->distinct()
            ->pluck('bank_name');
        
        return view('dues.checks-report', compact('allChecks', 'banks'));
    }

    public function encashCheck(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'encash_amount' => 'required|numeric|min:0.01',
            'next_due_date' => 'nullable|date|after_or_equal:today',
            'note' => 'nullable|string|max:500',
        ]);

        $payment = Payment::with(['bill.customer'])->find($id);

        if (!$payment || $payment->payment_type !== 'check' || $payment->status === 'encashed') {
            return redirect()->back()->with('error', 'Could not encash check');
        }

        $remainingCheck = $payment->remainingCheckAmount();

        if ($validated['encash_amount'] > $remainingCheck) {
            return redirect()->back()->with('error', 'Encash amount cannot exceed remaining check amount');
        }

        CheckEncashment::create([
            'payment_id' => $payment->id,
            'encash_amount' => $validated['encash_amount'],
            'encash_date' => now(),
            'next_due_date' => $validated['next_due_date'] ?? null,
            'note' => $validated['note'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $newEncashed = (float) $payment->encashed_amount + $validated['encash_amount'];
        $newRemaining = (float) $payment->check_amount - $newEncashed;

        $payment->update([
            'encashed_amount' => $newEncashed,
            'partially_encashed' => $newRemaining > 0,
            'status' => $newRemaining <= 0 ? 'encashed' : 'pending',
        ]);

        if ($validated['next_due_date']) {
            $payment->update(['check_reminder_date' => $validated['next_due_date']]);
        }

        $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => 'Cheque Encashed - Bill #' . ($payment->bill->bill_no ?? 'N/A'),
            'amount' => $validated['encash_amount'],
            'balance' => $lastBal + $validated['encash_amount'],
            'type' => 'credit',
            'note' => 'Cheque encashed: ' . ($payment->bank_name ?? 'N/A') . ' - ' . ($payment->check_no ?? 'N/A') . ' (Partial)',
            'user_id' => Auth::id(),
            'branch_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Cheque encashed successfully');
    }

    public function addPayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'due_id' => 'required|exists:dues,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:cash,check,mobile_banking',
            'next_due_date' => 'nullable|date|after_or_equal:today',
            'note' => 'nullable|string|max:500',
        ]);

        $due = Due::with('customer')->findOrFail($validated['due_id']);
        $remaining = $due->remaining_amount;

        if ($validated['payment_amount'] > $remaining) {
            return redirect()->back()->with('error', 'Payment amount cannot exceed remaining due amount');
        }

        $newRemaining = $remaining - $validated['payment_amount'];

        DuePayment::create([
            'due_id' => $due->id,
            'amount' => $validated['payment_amount'],
            'payment_type' => $validated['payment_type'],
            'payment_date' => now(),
            'remaining_amount' => $newRemaining,
            'note' => $validated['note'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $due->update([
            'amount' => $newRemaining,
            'status' => $newRemaining <= 0 ? 'paid' : 'pending',
        ]);

        if ($validated['next_due_date']) {
            $due->update(['due_date' => $validated['next_due_date']]);
        }

        $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => 'Due Payment - ' . ($due->customer->name ?? 'Customer'),
            'amount' => $validated['payment_amount'],
            'balance' => $lastBal + $validated['payment_amount'],
            'type' => 'credit',
            'note' => 'Partial payment via ' . $validated['payment_type'] . ' (Bill: ' . ($due->bill->bill_no ?? 'N/A') . ')',
            'user_id' => Auth::id(),
            'branch_id' => Auth::id(),
        ]);

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