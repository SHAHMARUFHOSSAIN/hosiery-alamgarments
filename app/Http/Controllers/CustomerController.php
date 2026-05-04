<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('id', 'desc')->paginate(15);
        $customers->appends($request->only('search', 'user_id'));

        return view('customers.index', compact('customers', 'users'));
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
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'] ?? null,
            'location' => $validated['location'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json($customer);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully');
    }

    public function show(Customer $customer): View
    {
        $this->authorizeCustomer($customer);

        $customer->load(['bills.user', 'creator']);

        return view('customers.show', compact('customer'));
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
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    private function authorizeCustomer(Customer $customer): void
    {
        // Everyone can view, edit, delete any customer
    }
}