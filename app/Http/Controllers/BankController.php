<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $query = Bank::with('creator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $banks = $query->orderBy('id', 'desc')->paginate(15);
        return view('banks.index', compact('banks'));
    }

    public function search(Request $request)
    {
        $term = $request->get('term', '');
        
        $banks = Bank::where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->select('id', 'name', 'branch', 'account_no')
            ->limit(20)
            ->get();

        return response()->json($banks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:255',
        ]);

        Bank::create([
            'name' => $validated['name'],
            'branch' => $validated['branch'] ?? null,
            'account_no' => $validated['account_no'] ?? null,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('banks.index')->with('success', 'Bank created successfully');
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $bank->update($validated);

        return redirect()->route('banks.index')->with('success', 'Bank updated successfully');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('banks.index')->with('success', 'Bank deleted successfully');
    }
}
