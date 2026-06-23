<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Payment;
use App\Models\TodaySalesReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $closedDates = [];
        if (!Auth::user()->isAdmin()) {
            $closedDates = TodaySalesReport::where('user_id', Auth::id())
                ->where('status', 'closed')
                ->pluck('report_date')
                ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->toDateString() : $d)
                ->values()
                ->toArray();
        }

        return view('bills.create', compact('closedDates'));
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
            'checks.*.check_photo' => 'nullable|image|max:5120',
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

        if (!Auth::user()->isAdmin()) {
            $closedReport = TodaySalesReport::where('user_id', Auth::id())
                ->where('report_date', $validated['report_date'])
                ->where('status', 'closed')
                ->exists();
            if ($closedReport) {
                return back()->withInput()->withErrors(['report_date' => 'Cannot create bill for this date. The sales report for ' . $validated['report_date'] . ' has already been closed. Contact admin for assistance.']);
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
            'due_date' => 'nullable|date',
            'tt_bank_name' => 'nullable|required_if:payment_type,tt|string|max:255',
            'tt_account_no' => 'nullable|required_if:payment_type,tt|string|max:255',
            'tt_amount' => 'nullable|required_if:payment_type,tt|numeric|min:0',
            'tt_date' => 'nullable|required_if:payment_type,tt|date',
            'card_reference' => 'nullable|required_if:payment_type,card|string|max:255',
            'card_location' => 'nullable|required_if:payment_type,card|string|max:255',
            'card_amount' => 'nullable|required_if:payment_type,card|numeric|min:0',
            'card_date' => 'nullable|required_if:payment_type,card|date',
            'checks' => 'nullable|array',
            'checks.*.bank_name' => 'nullable|required_if:payment_type,check|string|max:255',
            'checks.*.check_no' => 'nullable|required_if:payment_type,check|string|max:255',
            'checks.*.check_date' => 'nullable|required_if:payment_type,check|date',
            'checks.*.check_amount' => 'nullable|required_if:payment_type,check|numeric|min:0',
            'checks.*.check_reminder_date' => 'nullable|date',
            'checks.*.check_photo' => 'nullable|image|max:5120',
        ]);

        // Validate that at least one check is provided when payment type is check
        if ($request->payment_type === 'check') {
            $checks = $request->input('checks', []);
            if (empty($checks) || !is_array($checks) || count($checks) === 0) {
                return back()->withInput()->withErrors(['checks' => 'At least one cheque payment is required when payment type is cheque.']);
            }
        }

        $oldBillAmount = $bill->bill_amount;
        $oldDiscount = $bill->discount;
        $oldNetAmount = $oldBillAmount - $oldDiscount;

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

        $newNetAmount = $validated['bill_amount'] - ($validated['discount'] ?? 0);

        // Auto-scale payment amounts proportionally when bill amount/discount changes
        // Only applies when payment type hasn't changed (to avoid overriding intentional changes)
        $oldPayments = $bill->payments()->get();
        $cashAmount = $validated['payment_amount'] ?? 0;
        $ptype = $validated['payment_type'];
        $oldPtype = $oldPayments->firstWhere('payment_type', '!=', 'cash')?->payment_type ?? 'cash';
        if ($oldNetAmount > 0 && abs($oldNetAmount - $newNetAmount) > 0.001 && $ptype === $oldPtype) {
            $ratio = $newNetAmount / $oldNetAmount;
            $checkIdx = 0;
            foreach ($oldPayments as $op) {
                if ($op->payment_type !== $ptype && $op->payment_type !== 'cash') continue;
                $scaled = round($op->amount * $ratio, 2);
                if ($op->payment_type === 'cash') {
                    $cashAmount = $scaled;
                } elseif ($op->payment_type === 'check') {
                    if (isset($validated['checks'][$checkIdx])) {
                        $validated['checks'][$checkIdx]['check_amount'] = $scaled;
                    }
                    $checkIdx++;
                } elseif ($op->payment_type === 'tt') {
                    $validated['tt_amount'] = $scaled;
                } elseif ($op->payment_type === 'card') {
                    $validated['card_amount'] = $scaled;
                }
            }
        }
        // Cap individual amounts to new net
        foreach (['tt_amount', 'card_amount'] as $f) {
            if (($validated[$f] ?? 0) > $newNetAmount) $validated[$f] = $newNetAmount;
        }
        if (isset($validated['checks']) && is_array($validated['checks'])) {
            foreach ($validated['checks'] as $idx => $checkData) {
                if (($checkData['check_amount'] ?? 0) > $newNetAmount) {
                    $validated['checks'][$idx]['check_amount'] = $newNetAmount;
                }
            }
        }
        if ($cashAmount > $newNetAmount) $cashAmount = $newNetAmount;

        $netAmount = $newNetAmount;

        // --- Handle Payment Records ---

        // Determine effective amount and received amount based on payment type
        $effectiveAmount = match ($validated['payment_type']) {
            'tt' => $validated['tt_amount'] ?? $cashAmount,
            'card' => $validated['card_amount'] ?? $cashAmount,
            default => $cashAmount,
        };
        $isPendingPayment = in_array($validated['payment_type'], ['due', 'card', 'check']);

        // Delete all non-check payments to rebuild cleanly
        $bill->payments()->where('payment_type', '!=', 'check')->delete();

        // For check type, check payments + optional cash payment are created separately
        if ($validated['payment_type'] === 'check') {
            $bill->payments()->where('payment_type', 'check')->delete();

            if (!empty($validated['checks'])) {
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
                }
            }

            if ($cashAmount > 0) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'cash',
                    'amount' => $cashAmount,
                    'details' => $validated['payment_details'] ?? null,
                    'status' => 'encashed',
                ]);
            }
        } else {
            // Create separate cash payment for due/card types when cashAmount > 0
            if ($cashAmount > 0 && in_array($validated['payment_type'], ['card', 'due'])) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'payment_type' => 'cash',
                    'amount' => $cashAmount,
                    'details' => $validated['payment_details'] ?? null,
                    'status' => 'encashed',
                ]);
            }

            // Create the main payment record with type-specific fields
            $mainPaymentData = [
                'bill_id' => $bill->id,
                'payment_type' => $validated['payment_type'],
                'amount' => $effectiveAmount,
                'details' => $validated['payment_details'] ?? null,
                'status' => $isPendingPayment ? 'pending' : 'encashed',
            ];

            if ($validated['payment_type'] === 'tt') {
                $mainPaymentData['tt_bank_name'] = $validated['tt_bank_name'];
                $mainPaymentData['tt_account_no'] = $validated['tt_account_no'];
                $mainPaymentData['tt_amount'] = $validated['tt_amount'];
                $mainPaymentData['tt_date'] = $validated['tt_date'];
            } elseif ($validated['payment_type'] === 'card') {
                $mainPaymentData['card_reference'] = $validated['card_reference'];
                $mainPaymentData['card_location'] = $validated['card_location'];
                $mainPaymentData['card_amount'] = $validated['card_amount'];
                $mainPaymentData['card_date'] = $validated['card_date'];
            } elseif ($validated['payment_type'] === 'due') {
                $mainPaymentData['due_date'] = $validated['due_date'] ?? now()->addDays(7)->toDateString();
            }

            Payment::create($mainPaymentData);
        }

        // --- Recalculate Total Received and Due ---

        $totalCheckAmount = 0;
        if ($validated['payment_type'] === 'check' && !empty($validated['checks'])) {
            foreach ($validated['checks'] as $checkData) {
                $totalCheckAmount += (float) ($checkData['check_amount'] ?? 0);
            }
        }

        $totalReceived = match ($validated['payment_type']) {
            'card' => $cashAmount + (float) ($validated['card_amount'] ?? 0),
            'check' => $cashAmount + $totalCheckAmount,
            'due' => $cashAmount,
            default => $effectiveAmount,
        };

        $mainBalanceAmount = match ($validated['payment_type']) {
            'card', 'due', 'check' => $cashAmount,
            default => $effectiveAmount,
        };

        $dueAmount = $netAmount - $totalReceived;

        $existingDue = $bill->dues()->first();

        if ($dueAmount > 0) {
            $dueDate = $validated['due_date'] ?? now()->addDays(7)->toDateString();

            if ($existingDue) {
                $existingDue->update([
                    'amount' => $dueAmount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                ]);
            } else {
                Due::create([
                    'customer_id' => $bill->customer_id,
                    'bill_id' => $bill->id,
                    'amount' => $dueAmount,
                    'original_amount' => $dueAmount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'created_by' => Auth::id(),
                ]);
            }
        } elseif ($existingDue) {
            if (!$existingDue->hasPartialPayments()) {
                $existingDue->delete();
            } else {
                $existingDue->update(['status' => 'paid', 'amount' => 0]);
            }
        }

        // --- Reverse Cheque Encashed MainBalance entries (payments are being recreated) ---
        $chequeEncashEntries = MainBalance::where('name', 'like', 'Cheque Encashed - Bill #' . $bill->bill_no . '%')->get();
        foreach ($chequeEncashEntries as $ce) {
            $lastBal = MainBalance::where('branch_id', $ce->branch_id)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Reversal - Cheque Encashed Bill #' . $bill->bill_no . ' Edited',
                'amount' => $ce->amount,
                'balance' => $lastBal - $ce->amount,
                'type' => 'debit',
                'note' => 'Auto-reversal: Encashed cheque for Bill #' . $bill->bill_no . ' due to edit',
                'user_id' => Auth::id(),
                'branch_id' => $ce->branch_id,
            ]);
        }

        // --- Reverse Due Payment/Due Collection MainBalance entries on edit ---
        $dueMbEntries = MainBalance::where(function ($q) use ($bill) {
            $q->where('note', 'like', '%Bill: ' . $bill->bill_no . '%')
              ->orWhere('note', 'like', '%(Bill: ' . $bill->bill_no . ')%');
        })->get();
        foreach ($dueMbEntries as $de) {
            $lastBal = MainBalance::where('branch_id', $de->branch_id)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Reversal - Due Payment Bill #' . $bill->bill_no . ' Edited',
                'amount' => $de->amount,
                'balance' => $lastBal - $de->amount,
                'type' => 'debit',
                'note' => 'Auto-reversal: Due payment for Bill #' . $bill->bill_no . ' due to edit',
                'user_id' => Auth::id(),
                'branch_id' => $de->branch_id,
            ]);
        }

        // --- Adjust MainBalance ---

        $existingMainBalance = MainBalance::where('invoice_no', $bill->bill_no)
            ->where('name', 'like', 'Sales - Bill #%')
            ->first();

        if ($existingMainBalance && $existingMainBalance->amount != $mainBalanceAmount) {
            // Reverse the old MainBalance entry
            $lastBal = MainBalance::where('branch_id', $bill->user_id)->orderBy('id', 'desc')->value('balance') ?? 0;
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Reversal - Bill #' . $bill->bill_no . ' Edited',
                'amount' => $existingMainBalance->amount,
                'balance' => $lastBal - $existingMainBalance->amount,
                'type' => 'debit',
                'invoice_no' => $bill->bill_no,
                'note' => 'Auto-reversal: Bill #' . $bill->bill_no . ' was edited',
                'user_id' => Auth::id(),
                'branch_id' => $bill->user_id,
            ]);

            // Create new entry if amount was received
            if ($mainBalanceAmount > 0) {
                $lastBal = MainBalance::where('branch_id', $bill->user_id)->orderBy('id', 'desc')->value('balance') ?? 0;
                $customer = Customer::find($bill->customer_id);
                $note = 'Bill: ৳' . number_format($netAmount, 2) . ' | Received: ' . ($cashAmount > 0 ? 'Cash: ' . $cashAmount : $validated['payment_type']);
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
                    'branch_id' => $bill->user_id,
                ]);
            }
        } elseif (!$existingMainBalance && $mainBalanceAmount > 0) {
            // Brand new MainBalance entry
            $lastBal = MainBalance::where('branch_id', $bill->user_id)->orderBy('id', 'desc')->value('balance') ?? 0;
            $customer = Customer::find($bill->customer_id);
            $note = 'Bill: ৳' . number_format($netAmount, 2) . ' | Received: ' . ($cashAmount > 0 ? 'Cash: ' . $cashAmount : $validated['payment_type']);
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
                'branch_id' => $bill->user_id,
            ]);
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

        $deletedByBranch = [];

        // 1. Delete the Sales MainBalance entry
        $saleEntry = MainBalance::where('invoice_no', $bill->bill_no)
            ->where('name', 'like', 'Sales - Bill #%')
            ->first();

        if ($saleEntry) {
            $bid = $saleEntry->branch_id;
            $deletedByBranch[$bid]['total'] = ($deletedByBranch[$bid]['total'] ?? 0) + $saleEntry->amount;
            $deletedByBranch[$bid]['min_id'] = min($deletedByBranch[$bid]['min_id'] ?? PHP_INT_MAX, $saleEntry->id);
            $saleEntry->delete();
        }

        // 2. Delete Cheque Encashed entries
        $chequeEntries = MainBalance::where('name', 'like', 'Cheque Encashed - Bill #' . $bill->bill_no . '%')->get();

        foreach ($chequeEntries as $ce) {
            $bid = $ce->branch_id;
            $deletedByBranch[$bid]['total'] = ($deletedByBranch[$bid]['total'] ?? 0) + $ce->amount;
            $deletedByBranch[$bid]['min_id'] = min($deletedByBranch[$bid]['min_id'] ?? PHP_INT_MAX, $ce->id);
            $ce->delete();
        }

        // 3. Delete Due Payment/Due Collection entries linked to this bill
        $dueEntries = MainBalance::where(function ($q) use ($bill) {
            $q->where('note', 'like', '%Bill: ' . $bill->bill_no . '%')
              ->orWhere('note', 'like', '%(Bill: ' . $bill->bill_no . ')%');
        })->get();

        foreach ($dueEntries as $de) {
            $bid = $de->branch_id;
            $deletedByBranch[$bid]['total'] = ($deletedByBranch[$bid]['total'] ?? 0) + $de->amount;
            $deletedByBranch[$bid]['min_id'] = min($deletedByBranch[$bid]['min_id'] ?? PHP_INT_MAX, $de->id);
            $de->delete();
        }

        // Recalculate running balances for affected branches
        foreach ($deletedByBranch as $bid => $data) {
            if ($data['min_id'] !== PHP_INT_MAX && abs($data['total']) > 0.001) {
                MainBalance::where('branch_id', $bid)
                    ->where('id', '>', $data['min_id'])
                    ->decrement('balance', $data['total']);
            }
        }

        // Delete the bill (cascades to payments, dues, check_encashments, due_payments)
        $bill->delete();

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted permanently with all associated entries.');
    }
}