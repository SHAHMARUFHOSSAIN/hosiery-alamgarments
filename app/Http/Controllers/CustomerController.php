<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PreviousDue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::with('creator');

        if (Auth::user()->isAdmin()) {
            $users = \App\Models\User::where('role', 'user')->get(['id', 'name']);
            if ($request->filled('user_id')) {
                $query->where('created_by', $request->user_id);
            }
        } else {
            $users = \App\Models\User::where('role', 'user')->get(['id', 'name']);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('id', 'desc')->paginate(15);
        $customers->appends($request->only('search', 'user_id', 'location'));

        $locations = Customer::distinct()->orderBy('location')->pluck('location')->filter()->values();

        $totalCustomers = Customer::query();
        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $totalCustomers->where('created_by', $request->user_id);
            }
        } else {
            $totalCustomers->where('created_by', Auth::id());
        }
        if ($request->filled('location')) {
            $totalCustomers->where('location', $request->location);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $totalCustomers->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        $totalCustomers = $totalCustomers->count();

        return view('customers.index', compact('customers', 'users', 'totalCustomers', 'locations'));
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->term;
        
        $customers = Customer::where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('mobile', 'like', "%{$term}%");
        })
        ->limit(10)
        ->get(['id', 'name', 'mobile', 'location']);

        return response()->json($customers);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $openingBalance = $validated['opening_balance'] ?? 0;

        $customer = Customer::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'] ?? null,
            'location' => $validated['location'] ?? null,
            'opening_balance' => $openingBalance,
            'created_by' => Auth::id(),
        ]);

        $this->syncPreviousDue($customer, $openingBalance);

        if ($request->expectsJson()) {
            return response()->json($customer);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully');
    }

    public function show(Request $request, Customer $customer): View
    {
        $this->authorizeCustomer($customer);

        $customer->load(['creator']);

        $billQuery = $customer->bills()->with(['user', 'editor']);

        if ($request->filled('date_from')) {
            $billQuery->whereDate('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $billQuery->whereDate('report_date', '<=', $request->date_to);
        }
        if (Auth::user()->isAdmin() && $request->filled('user_id')) {
            $billQuery->where('user_id', $request->user_id);
        }

        $totalBuy = (clone $billQuery)->sum('bill_amount');

        $bills = $billQuery->latest()->paginate(15);

        $dueQuery = $customer->dues();
        if ($request->filled('date_from')) {
            $dueQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $dueQuery->whereDate('created_at', '<=', $request->date_to);
        }
        if (Auth::user()->isAdmin() && $request->filled('user_id')) {
            $dueQuery->where('created_by', $request->user_id);
        }

        $pendingDues = $dueQuery->where('status', 'pending')->get();
        $totalDue = $pendingDues->sum('amount') + $customer->opening_balance;

        $users = Auth::user()->isAdmin() ? \App\Models\User::where('role', 'user')->get(['id', 'name']) : collect();

        return view('customers.show', compact('customer', 'totalBuy', 'totalDue', 'bills', 'users'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorizeCustomer($customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $customer->update($validated);

        $this->syncPreviousDue($customer, $validated['opening_balance'] ?? 0);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

    public function updateOpeningBalance(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $customer->update(['opening_balance' => $validated['opening_balance']]);

        $this->syncPreviousDue($customer, $validated['opening_balance']);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Opening balance updated successfully');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    private function syncPreviousDue(Customer $customer, float $amount): void
    {
        if ($amount > 0) {
            PreviousDue::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                ],
                [
                    'amount' => $amount,
                    'original_amount' => $amount,
                    'notes' => 'Auto-created from opening balance',
                    'created_by' => Auth::id(),
                ]
            );
        } else {
            PreviousDue::where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->update(['status' => 'paid']);
        }
    }

    private function authorizeCustomer(Customer $customer): void
    {
        // Everyone can view, edit, delete any customer
    }
}