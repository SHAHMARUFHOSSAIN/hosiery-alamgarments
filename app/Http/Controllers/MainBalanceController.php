<?php

namespace App\Http\Controllers;

use App\Models\MainBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MainBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MainBalance::with(['user', 'branch']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            $query->where('branch_id', Auth::id());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $balances = $query->orderBy('id', 'desc')->paginate(20);
        
        $totalCredit = $query->clone()->where('type', 'credit')->sum('amount');
        $totalDebit = $query->clone()->where('type', 'debit')->sum('amount');
        $mainBalance = $totalCredit - $totalDebit;

        $branches = User::where('role', 'user')->get(['id', 'name']);

        return view('main-balance.index', compact('balances', 'mainBalance', 'totalCredit', 'totalDebit', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:credit,debit',
            'note' => 'nullable|string',
        ]);

        MainBalance::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'type' => $request->type,
            'note' => $request->note,
            'user_id' => Auth::id(),
            'branch_id' => Auth::user()->isAdmin() ? ($request->branch_id ?? Auth::id()) : Auth::id(),
        ]);

        return redirect()->route('main-balance.index')->with('success', 'Transaction recorded successfully.');
    }

    public function balanceReport(Request $request): View
    {
        $query = MainBalance::with(['user', 'branch']);

        if (!Auth::user()->isAdmin()) {
            $query->where('branch_id', Auth::id());
        } else if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $balances = $query->orderBy('id', 'desc')->get();

        $branchWise = [];
        $branches = User::where('role', 'user')->get(['id', 'name']);

        foreach ($branches as $branch) {
            $credit = MainBalance::where('branch_id', $branch->id)->where('type', 'credit')->sum('amount');
            $debit = MainBalance::where('branch_id', $branch->id)->where('type', 'debit')->sum('amount');
            $branchWise[$branch->id] = [
                'name' => $branch->name,
                'credit' => $credit,
                'debit' => $debit,
                'balance' => $credit - $debit,
            ];
        }

        $totalCredit = $balances->where('type', 'credit')->sum('amount');
        $totalDebit = $balances->where('type', 'debit')->sum('amount');
        $totalMainBalance = $totalCredit - $totalDebit;

        return view('main-balance.report', compact('balances', 'branchWise', 'totalMainBalance', 'totalCredit', 'totalDebit', 'branches'));
    }
}