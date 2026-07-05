<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\Customer;
use App\Models\MainBalance;
use App\Models\PreviousDue;
use App\Models\PreviousDuePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PreviousDueController extends Controller
{
    public function index(Request $request): View
    {
        $query = PreviousDue::with(['customer', 'creator']);

        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $previousDues = $query->latest()->paginate(20);

        return view('previous-dues.index', compact('previousDues'));
    }

    public function create(): View
    {
        $customers = Customer::where('is_active', true)
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('created_by', Auth::id()))
            ->orderBy('name')
            ->get(['id', 'name', 'mobile']);

        return view('previous-dues.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        PreviousDue::create([
            'customer_id' => $validated['customer_id'],
            'amount' => $validated['amount'],
            'original_amount' => $validated['amount'],
            'notes' => $validated['notes'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('previous-dues.index')
            ->with('success', 'Previous due created successfully');
    }

    public function show(PreviousDue $previousDue): View
    {
        $previousDue->load(['customer', 'creator', 'payments.user']);

        return view('previous-dues.show', compact('previousDue'));
    }

    public function edit(PreviousDue $previousDue): View
    {
        $customers = Customer::where('is_active', true)
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('created_by', Auth::id()))
            ->orderBy('name')
            ->get(['id', 'name', 'mobile']);

        return view('previous-dues.edit', compact('previousDue', 'customers'));
    }

    public function update(Request $request, PreviousDue $previousDue): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,paid',
            'notes' => 'nullable|string|max:1000',
        ]);

        $previousDue->update($validated);

        return redirect()->route('previous-dues.index')
            ->with('success', 'Previous due updated successfully');
    }

    public function destroy(PreviousDue $previousDue): RedirectResponse
    {
        $previousDue->delete();

        return redirect()->route('previous-dues.index')
            ->with('success', 'Previous due deleted successfully');
    }

    public function addPayment(Request $request): RedirectResponse
    {
        $rules = [
            'previous_due_id' => 'required|exists:previous_dues,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,mobile_banking',
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

        $previousDue = PreviousDue::with('customer')->findOrFail($validated['previous_due_id']);
        $remaining = $previousDue->remaining_amount;
        $discount = (float) ($validated['discount'] ?? 0);

        if ($validated['payment_amount'] + $discount > $remaining) {
            return redirect()->back()->with('error', 'Payment amount plus discount cannot exceed remaining due amount');
        }

        $newRemaining = $remaining - $validated['payment_amount'] - $discount;

        $payData = [
            'previous_due_id' => $previousDue->id,
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

        PreviousDuePayment::create($payData);

        if ($request->payment_type !== 'check') {
            $previousDue->update([
                'amount' => $newRemaining,
                'status' => $newRemaining <= 0 ? 'paid' : 'pending',
            ]);

            $customer = $previousDue->customer;
            if ($customer) {
                $newOpeningBalance = max(0, $customer->opening_balance - $validated['payment_amount']);
                $customer->update(['opening_balance' => $newOpeningBalance]);
            }

            $lastBal = MainBalance::where('branch_id', Auth::id())->orderBy('id', 'desc')->value('balance') ?? 0;
            $mainBalanceNote = 'Payment via ' . $validated['payment_type'];
            if ($validated['transaction_id'] ?? null) {
                $mainBalanceNote .= ' | TxnID: ' . $validated['transaction_id'];
            }
            MainBalance::create([
                'voucher_no' => VoucherHelper::generateVoucherNo(),
                'name' => 'Previous Due Payment - ' . ($customer->name ?? 'Customer'),
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
                    'name' => 'Discount - ' . ($customer->name ?? 'Customer'),
                    'amount' => $discount,
                    'balance' => $newBal - $discount,
                    'type' => 'debit',
                    'note' => 'Discount on previous due payment',
                    'user_id' => Auth::id(),
                    'branch_id' => Auth::id(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Payment recorded successfully');
    }
}
