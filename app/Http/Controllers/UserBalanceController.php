<?php

namespace App\Http\Controllers;

use App\Models\MainBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MainBalance::where('branch_id', Auth::id());

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $balances = $query->orderBy('id', 'desc')->paginate(15);
        
        $totalCredit = MainBalance::where('branch_id', Auth::id())->where('type', 'credit')->sum('amount');
        $totalDebit = MainBalance::where('branch_id', Auth::id())->where('type', 'debit')->sum('amount');
        $mainBalance = $totalCredit - $totalDebit;

        return view('user-balance.index', compact('balances', 'mainBalance', 'totalCredit', 'totalDebit'));
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
            'branch_id' => Auth::id(),
        ]);

        return redirect()->route('user-balance.index')->with('success', 'Transaction recorded successfully.');
    }
}