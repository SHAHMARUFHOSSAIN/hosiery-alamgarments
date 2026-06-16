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
        $validated = $request->validate([
            'previous_due_id' => 'required|exists:previous_dues,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:cash,check,mobile_banking',
            'bank_name' => 'nullable|string|max:255',
            'check_no' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $previousDue = PreviousDue::with('customer')->findOrFail($validated['previous_due_id']);
        $remaining = $previousDue->remaining_amount;

        if ($validated['payment_amount'] > $remaining) {
            return redirect()->back()->with('error', 'Payment amount cannot exceed remaining due amount');
        }

        $newRemaining = $remaining - $validated['payment_amount'];

        PreviousDuePayment::create([
            'previous_due_id' => $previousDue->id,
            'amount' => $validated['payment_amount'],
            'payment_type' => $validated['payment_type'],
            'payment_date' => now(),
            'remaining_amount' => $newRemaining,
            'note' => $validated['note'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'check_no' => $validated['check_no'] ?? null,
            'user_id' => Auth::id(),
        ]);

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
        $mainBalanceNote = 'Payment via ' . $validated['payment_type'] . ' (' . ($validated['bank_name'] ? $validated['bank_name'] . ' - ' . $validated['check_no'] : '') . ')';
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

        return redirect()->back()->with('success', 'Payment recorded successfully');
    }
}
