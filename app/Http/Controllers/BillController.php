<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $query = Bill::with(['customer', 'user', 'payments']);

        if (Auth::user()->isAdmin()) {
            $users = \App\Models\User::where('role', 'user')->get(['id', 'name']);
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
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
        $allowedSorts = ['id', 'bill_no', 'shop_name', 'bill_man', 'bill_amount', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $bills = $query->paginate(15);
        $bills->appends($request->only('search', 'user_id', 'date_from', 'date_to', 'bill_man', 'sort', 'direction'));

        return view('bills.index', compact('bills', 'users'));
    }

    public function create(): View
    {
        return view('bills.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bill_no' => 'required',
            'shop_name' => 'nullable|string|max:255',
            'bill_man' => 'nullable|string|max:255',
            'bill_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,tt,card,due',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_details' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
            'bank_name' => 'nullable|string|max:255',
            'check_no' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
            'check_reminder_date' => 'nullable|date',
            'check_amount' => 'nullable|numeric|min:0',
            'check_photo' => 'nullable|image|max:2048',
            'tt_bank_name' => 'nullable|string|max:255',
            'tt_account_no' => 'nullable|string|max:255',
            'tt_amount' => 'nullable|numeric|min:0',
            'tt_date' => 'nullable|date',
            'card_name' => 'nullable|string|max:255',
            'card_location' => 'nullable|string|max:255',
            'card_amount' => 'nullable|numeric|min:0',
            'card_date' => 'nullable|date',
        ]);

        $bill = Bill::create([
            'bill_no' => $validated['bill_no'],
            'customer_id' => $validated['customer_id'],
            'shop_name' => $validated['shop_name'] ?? null,
            'bill_man' => $validated['bill_man'] ?? null,
            'bill_amount' => $validated['bill_amount'],
            'discount' => $validated['discount'] ?? 0,
            'user_id' => Auth::id(),
        ]);

        $paymentAmount = $validated['payment_amount'] ?? 0;
        $dueDate = $validated['due_date'] ?? now()->addDays(7)->toDateString();

        $checkPhotoPath = null;
        if ($request->hasFile('check_photo')) {
            $checkPhotoPath = $request->file('check_photo')->store('check-photos', 'public');
        }

        Payment::create([
            'bill_id' => $bill->id,
            'payment_type' => $validated['payment_type'],
            'amount' => $paymentAmount,
            'details' => $validated['payment_details'] ?? null,
            'bank_name' => $validated['payment_type'] === 'check' ? $validated['bank_name'] : null,
            'check_no' => $validated['payment_type'] === 'check' ? $validated['check_no'] : null,
            'check_date' => $validated['payment_type'] === 'check' ? $validated['check_date'] : null,
            'check_reminder_date' => $validated['payment_type'] === 'check' ? $validated['check_reminder_date'] : null,
            'check_amount' => $validated['payment_type'] === 'check' ? $validated['check_amount'] : null,
            'status' => $validated['payment_type'] === 'check' ? 'pending' : 'encashed',
            'check_photo' => $checkPhotoPath,
            'tt_bank_name' => $validated['payment_type'] === 'tt' ? $validated['tt_bank_name'] : null,
            'tt_account_no' => $validated['payment_type'] === 'tt' ? $validated['tt_account_no'] : null,
            'tt_amount' => $validated['payment_type'] === 'tt' ? $validated['tt_amount'] : null,
            'tt_date' => $validated['payment_type'] === 'tt' ? $validated['tt_date'] : null,
            'card_name' => $validated['payment_type'] === 'card' ? $validated['card_name'] : null,
            'card_location' => $validated['payment_type'] === 'card' ? $validated['card_location'] : null,
            'card_amount' => $validated['payment_type'] === 'card' ? $validated['card_amount'] : null,
            'card_date' => $validated['payment_type'] === 'card' ? $validated['card_date'] : null,
            'due_date' => $validated['payment_type'] === 'due' ? $dueDate : null,
        ]);

        $netAmount = $validated['bill_amount'] - ($validated['discount'] ?? 0);

        if ($paymentAmount > 0) {
            MainBalance::create([
                'name' => 'Sales - Bill #' . $bill->bill_no,
                'amount' => $paymentAmount,
                'type' => 'credit',
                'note' => 'Received: ' . $validated['payment_type'],
                'user_id' => Auth::id(),
                'branch_id' => Auth::id(),
            ]);
        }

        if ($validated['payment_type'] === 'due') {
            $dueAmount = $netAmount - $paymentAmount;

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
        }

        return redirect()->route('bills.index')
            ->with('success', 'Bill created successfully');
    }

    public function show(Bill $bill): View
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $bill->load(['customer', 'user', 'payments', 'dues.duePayments.user']);

        return view('bills.show', compact('bill'));
    }

    public function edit(Bill $bill): View
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'bill_no' => 'required',
            'shop_name' => 'nullable|string|max:255',
            'bill_man' => 'nullable|string|max:255',
            'bill_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $bill->update($validated);

        return redirect()->route('bills.index')
            ->with('success', 'Bill updated successfully');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $bill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $bill->delete();

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully');
    }
}