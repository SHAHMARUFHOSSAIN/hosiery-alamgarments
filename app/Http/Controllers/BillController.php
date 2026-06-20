<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $query = Bill::with(['customer', 'user', 'editor', 'payments']);
        

        if (Auth::user()->isAdmin()) {
            $users = \App\Models\User::where('role', 'user')->get(['id', 'name']);
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('report_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('report_date', '<=', $request->date_to);
            }
        } else {
            $query->where('user_id', Auth::id());
            $users = collect();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bill_man')) {
            $query->where('bill_man', 'like', "%{$request->bill_man}%");
        }

        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'bill_no', 'shop_name', 'bill_man', 'bill_amount', 'report_date'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $bills = $query->paginate(15);
        $bills->appends($request->only('search', 'user_id', 'date_from', 'date_to', 'bill_man', 'sort', 'direction'));

        $totalBills = Bill::query();
        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $totalBills->where('user_id', $request->user_id);
            }
            if ($request->filled('date_from')) {
                $totalBills->whereDate('report_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $totalBills->whereDate('report_date', '<=', $request->date_to);
            }
        } else {
            $totalBills->where('user_id', Auth::id());
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $totalBills->where(function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('bill_man', 'like', "%{$search}%");
            });
        }
        if ($request->filled('bill_man')) {
            $totalBills->where('bill_man', 'like', "%{$request->bill_man}%");
        }
        $totalBills = $totalBills->count();

        return view('bills.index', compact('bills', 'users', 'totalBills'));
    }

    public function create(): View
    {
        return view('bills.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bill_no' => 'required',
            'shop_name' => 'nullable|string|max:255',
            'bill_man' => 'nullable|string|max:255',
            'bill_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,tt,card,due',
            'payment_amount' => 'nullable|required_if:payment_type,cash|numeric|min:0',
            'payment_details' => 'nullable|string',
            'report_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:today',
            'checks' => 'nullable|array',
            'checks.*.bank_name' => 'nullable|required_if:payment_type,check|string|max:255',
            'checks.*.check_no' => 'nullable|required_if:payment_type,check|string|max:255',
            'checks.*.check_date' => 'nullable|required_if:payment_type,check|date',
            'checks.*.check_amount' => 'nullable|required_if:payment_type,check|numeric|min:0',
            'checks.*.check_reminder_date' => 'nullable|date',
            'checks.*.check_photo' => 'nullable|image|max:2048',
            'tt_bank_name' => 'nullable|required_if:payment_type,tt|string|max:255',
            'tt_account_no' => 'nullable|required_if:payment_type,tt|string|max:255',
            'tt_amount' => 'nullable|required_if:payment_type,tt|numeric|min:0',
            'tt_date' => 'nullable|required_if:payment_type,tt|date',
            'card_reference' => 'nullable|required_if:payment_type,card|string|max:255',
            'card_location' => 'nullable|required_if:payment_type,card|string|max:255',
            'card_amount' => 'nullable|required_if:payment_type,card|numeric|min:0',
            'card_date' => 'nullable|required_if:payment_type,card|date',
        ]);

        // Validate that at least one check is provided when payment type is check
        if ($request->payment_type === 'check') {
            $checks = $request->input('checks', []);
            if (empty($checks) || !is_array($checks) || count($checks) === 0) {
                return back()->withInput()->withErrors(['checks' => 'At least one cheque payment is required when payment type is cheque.']);
            }
        }

        $bill = Bill::create([
            'bill_no' => $validated['bill_no'],
            'customer_id' => $validated['customer_id'],
            'shop_name' => $validated['shop_name'] ?? null,
            'bill_man' => $validated['bill_man'] ?? null,
            'bill_amount' => $validated['bill_amount'],
            'discount' => $validated['discount'] ?? 0,
            'report_date' => $validated['report_date'],
            'user_id' => Auth::id(),
        ]);

        $cashAmount = $validated['payment_amount'] ?? 0;
        $dueDate = $validated['due_date'] ?? now()->addDays(7)->toDateString();
        $totalCheckAmount = 0;
        $totalReceived = 0;
        $mainBalanceAmount = 0;

        if ($validated['payment_type'] === 'check' && isset($validated['checks'])) {
            foreach ($validated['checks'] as $index => $checkData) {
                $checkPhotoPath = null;
                if ($request->hasFile("checks.{$index}.check_photo")) {
                    $checkPhotoPath = $request->file("checks.{$index}.check_photo")->store('cheque', 'public');
                }

                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'check',
                    'amount' => $checkData['check_amount'],
                    'details' => $validated['payment_details'] ?? null,
                    'bank_name' => $checkData['bank_name'],
                    'check_no' => $checkData['check_no'],
                    'check_date' => $checkData['check_date'],
                    'check_reminder_date' => $checkData['check_reminder_date'] ?? null,
                    'check_amount' => $checkData['check_amount'],
                    'status' => 'pending',
                    'check_photo' => $checkPhotoPath,
                ]);

                $totalCheckAmount += $checkData['check_amount'];
            }

            if ($cashAmount > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'cash',
                    'amount' => $cashAmount,
                    'details' => $validated['payment_details'] ?? null,
                    'status' => 'encashed',
                ]);
                $totalReceived += $cashAmount;
                $mainBalanceAmount += $cashAmount;
            }

            $totalReceived += $totalCheckAmount;
        } else {
            $effectiveAmount = match ($validated['payment_type']) {
                'tt' => $validated['tt_amount'] ?? $cashAmount,
                'card' => $validated['card_amount'] ?? $cashAmount,
                default => $cashAmount,
            };

            $isPendingPayment = in_array($validated['payment_type'], ['due', 'card']);

            // For card/due types, handle cash (Payment Received) separately so it goes to main balance
            if ($cashAmount > 0 && in_array($validated['payment_type'], ['card', 'due'])) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'cash',
                    'amount' => $cashAmount,
                    'details' => $validated['payment_details'] ?? null,
                    'status' => 'encashed',
                ]);
            }

            Payment::create([
                'bill_id' => $bill->id,
                'payment_type' => $validated['payment_type'],
                'amount' => $effectiveAmount,
                'details' => $validated['payment_details'] ?? null,
                'status' => $isPendingPayment ? 'pending' : 'encashed',
                'tt_bank_name' => $validated['payment_type'] === 'tt' ? $validated['tt_bank_name'] : null,
                'tt_account_no' => $validated['payment_type'] === 'tt' ? $validated['tt_account_no'] : null,
                'tt_amount' => $validated['payment_type'] === 'tt' ? $validated['tt_amount'] : null,
                'tt_date' => $validated['payment_type'] === 'tt' ? $validated['tt_date'] : null,
                'card_reference' => $validated['payment_type'] === 'card' ? $validated['card_reference'] : null,
                'card_location' => $validated['payment_type'] === 'card' ? $validated['card_location'] : null,
                'card_amount' => $validated['payment_type'] === 'card' ? $validated['card_amount'] : null,
                'card_date' => $validated['payment_type'] === 'card' ? $validated['card_date'] : null,
                'due_date' => $validated['payment_type'] === 'due' ? $dueDate : null,
            ]);

            if ($validated['payment_type'] === 'card') {
                $totalReceived = $cashAmount + (float) $validated['card_amount'];
                $mainBalanceAmount = $cashAmount;
            } elseif ($validated['payment_type'] === 'due') {
                $totalReceived = $cashAmount;
                $mainBalanceAmount = $cashAmount;
            } else {
                $totalReceived = $effectiveAmount;
                $mainBalanceAmount = $effectiveAmount;
            }
        }

        $netAmount = $validated['bill_amount'] - ($validated['discount'] ?? 0);

        if ($mainBalanceAmount > 0) {
            $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
            $customer = Customer::find($validated['customer_id']);
            $note = 'Bill: ৳' . number_format($netAmount, 2) . ' | Received: ' . ($cashAmount > 0 ? 'Cash: ' . $cashAmount : $validated['payment_type']);
            if ($totalCheckAmount > 0) {
                $note .= ' (Cheques pending: ' . number_format($totalCheckAmount, 2) . ')';
            }
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Sales - Bill #' . $bill->bill_no,
                'amount' => $mainBalanceAmount,
                'balance' => $lastBal + $mainBalanceAmount,
                'type' => 'credit',
                'invoice_no' => $bill->bill_no,
                'party_name' => $customer?->name,
                'note' => $note,
                'user_id' => Auth::id(),
                'branch_id' => Auth::id(),
            ]);
        }

        $dueAmount = $netAmount - $totalReceived;

        if ($dueAmount > 0) {
            Due::create([
                'customer_id' => $validated['customer_id'],
                'bill_id' => $bill->id,
                'amount' => $dueAmount,
                'original_amount' => $dueAmount,
                'due_date' => $dueDate,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);
        }

        // Return JSON for AJAX requests, redirect for standard form submission
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully',
                'bill_id' => $bill->id,
                'redirect_url' => route('bills.index')
            ]);
        }

        return redirect()->route('bills.index')
            ->with('success', 'Bill created successfully');
    }

    public function show(Bill $bill): View
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $bill->load(['customer', 'user', 'editor', 'payments.checkEncashments', 'dues.duePayments.user']);

        return view('bills.show', compact('bill'));
    }

    public function edit(Bill $bill): View
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$bill->isEditable()) {
            abort(403, 'Bills can only be edited within 24 hours of creation. Contact an admin.');
        }

        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$bill->isEditable()) {
            return redirect()->route('bills.index')
                ->with('error', 'Bills can only be edited within 24 hours of creation. Contact an admin.');
        }

        $validated = $request->validate([
            'bill_no' => 'required',
            'shop_name' => 'nullable|string|max:255',
            'bill_man' => 'nullable|string|max:255',
            'report_date' => 'required|date',
            'bill_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,tt,card,due',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_details' => 'nullable|string',
            'check_bank_name' => 'nullable|string|max:255',
            'check_no' => 'nullable|string|max:100',
            'check_amount' => 'nullable|numeric|min:0',
            'check_date' => 'nullable|date',
            'check_reminder_date' => 'nullable|date',
            'check_photo' => 'nullable|image|max:2048',
        ]);

        $bill->update([
            'bill_no' => $validated['bill_no'],
            'shop_name' => $validated['shop_name'] ?? null,
            'bill_man' => $validated['bill_man'] ?? null,
            'report_date' => $validated['report_date'],
            'bill_amount' => $validated['bill_amount'],
            'discount' => $validated['discount'] ?? 0,
            'edited_at' => now(),
            'edited_by' => Auth::id(),
        ]);

        $firstPayment = $bill->payments()->first();
        if ($firstPayment) {
            $firstPayment->update([
                'payment_type' => $validated['payment_type'],
                'amount' => $validated['payment_amount'] ?? 0,
                'details' => $validated['payment_details'] ?? null,
            ]);
        }

        $checkPayment = $bill->payments()->where('payment_type', 'check')->first();
        if ($checkPayment) {
            $updateData = [
                'bank_name' => $validated['check_bank_name'] ?? $checkPayment->bank_name,
                'check_no' => $validated['check_no'] ?? $checkPayment->check_no,
                'check_amount' => $validated['check_amount'] ?? $checkPayment->check_amount,
                'check_date' => $validated['check_date'] ?? $checkPayment->check_date,
                'check_reminder_date' => $validated['check_reminder_date'] ?? $checkPayment->check_reminder_date,
            ];

            if ($request->hasFile('check_photo')) {
                $path = $request->file('check_photo')->store('cheque', 'public');
                $updateData['check_photo'] = $path;
            }

            $checkPayment->update($updateData);
        }

        return redirect()->route('bills.index')
            ->with('success', 'Bill updated successfully');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$bill->isDeletable()) {
            return redirect()->route('bills.index')
                ->with('error', 'Bills can only be deleted within 24 hours of creation. Contact an admin.');
        }

        $branchId = $bill->user_id;

        $saleEntry = MainBalance::where('invoice_no', $bill->bill_no)
            ->where('name', 'like', 'Sales - Bill #%')
            ->first();

        if ($saleEntry) {
            $lastBal = MainBalance::where('branch_id', $branchId)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Reversal - Bill #' . $bill->bill_no . ' Deleted',
                'amount' => $saleEntry->amount,
                'balance' => $lastBal - $saleEntry->amount,
                'type' => 'debit',
                'invoice_no' => $bill->bill_no,
                'note' => 'Auto-reversal: Bill #' . $bill->bill_no . ' was deleted',
                'user_id' => Auth::id(),
                'branch_id' => $branchId,
            ]);
        }

        $duePayments = MainBalance::where('name', 'like', 'Due Payment -%')
            ->where('note', 'like', '%Bill: ' . $bill->bill_no . '%')
            ->orWhere('note', 'like', '%(Bill: ' . $bill->bill_no . ')%')
            ->get();

        foreach ($duePayments as $dp) {
            $lastBal = MainBalance::where('branch_id', $dp->branch_id)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Reversal - Due Payment Bill #' . $bill->bill_no . ' Deleted',
                'amount' => $dp->amount,
                'balance' => $lastBal - $dp->amount,
                'type' => 'debit',
                'note' => 'Auto-reversal: Due payment for deleted Bill #' . $bill->bill_no,
                'user_id' => Auth::id(),
                'branch_id' => $dp->branch_id,
            ]);
        }

        $chequeEntries = MainBalance::where('name', 'like', 'Cheque Encashed - Bill #' . $bill->bill_no . '%')->get();

        foreach ($chequeEntries as $ce) {
            $lastBal = MainBalance::where('branch_id', $ce->branch_id)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Reversal - Cheque Encashed Bill #' . $bill->bill_no . ' Deleted',
                'amount' => $ce->amount,
                'balance' => $lastBal - $ce->amount,
                'type' => 'debit',
                'note' => 'Auto-reversal: Cheque encashment for deleted Bill #' . $bill->bill_no,
                'user_id' => Auth::id(),
                'branch_id' => $ce->branch_id,
            ]);
        }

        $bill->delete();

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully. Related MainBalance entries have been reversed.');
    }
}