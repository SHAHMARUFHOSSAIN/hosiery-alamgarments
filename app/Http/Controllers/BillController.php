<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
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
        $query = Bill::with(['customer', 'user']);

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
                  ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }

        $bills = $query->orderBy('id', 'desc')->paginate(15);
        $bills->appends($request->only('search', 'user_id', 'date_from', 'date_to'));

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
            'bill_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,check,tt,due',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_details' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $bill = Bill::create([
            'bill_no' => $validated['bill_no'],
            'customer_id' => $validated['customer_id'],
            'shop_name' => $validated['shop_name'] ?? null,
            'bill_amount' => $validated['bill_amount'],
            'discount' => $validated['discount'] ?? 0,
            'user_id' => Auth::id(),
        ]);

        $paymentAmount = $validated['payment_amount'] ?? 0;
        $dueDate = $validated['due_date'] ?? now()->addDays(7)->toDateString();

        Payment::create([
            'bill_id' => $bill->id,
            'payment_type' => $validated['payment_type'],
            'amount' => $paymentAmount,
            'details' => $validated['payment_details'] ?? null,
            'due_date' => $validated['payment_type'] === 'due' ? $dueDate : null,
        ]);

        if ($validated['payment_type'] === 'due') {
            $netAmount = $validated['bill_amount'] - ($validated['discount'] ?? 0);
            $dueAmount = $netAmount - $paymentAmount;

            if ($dueAmount > 0) {
                Due::create([
                    'customer_id' => $validated['customer_id'],
                    'bill_id' => $bill->id,
                    'amount' => $dueAmount,
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

        $bill->load(['customer', 'user', 'payments', 'dues']);

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