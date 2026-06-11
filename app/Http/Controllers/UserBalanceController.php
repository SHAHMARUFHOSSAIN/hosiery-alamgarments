<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\MainBalance;
use App\Models\Setting;
use App\Models\User;
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
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('invoice_no', 'like', "%{$s}%")
                  ->orWhere('party_name', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('note', 'like', "%{$s}%");
            });
        }

        $balances = $query->orderBy('id', 'desc')->paginate(15);

        $totalCredit = MainBalance::where('branch_id', Auth::id())->where('type', 'credit')->sum('amount');
        $totalDebit = MainBalance::where('branch_id', Auth::id())->where('type', 'debit')->sum('amount');
        $mainBalance = $totalCredit - $totalDebit;

        $user = Auth::user();

        return view('user-balance.index', compact('balances', 'mainBalance', 'totalCredit', 'totalDebit', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:credit,debit',
            'note' => 'nullable|string',
            'invoice_no' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'party_name' => 'nullable|string|max:255',
        ]);

        $branchId = Auth::id();
        $lastBalance = MainBalance::where('branch_id', $branchId)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        $newBalance = $request->type === 'credit'
            ? $lastBalance + $request->amount
            : $lastBalance - $request->amount;

        MainBalance::create([
            'voucher_no' => VoucherHelper::generateVoucherNo(),
            'name' => $request->name,
            'amount' => $request->amount,
            'balance' => $newBalance,
            'type' => $request->type,
            'note' => $request->note,
            'invoice_no' => $request->invoice_no,
            'reference' => $request->reference,
            'party_name' => $request->party_name,
            'user_id' => Auth::id(),
            'branch_id' => $branchId,
        ]);

        return redirect()->route('user-balance.index')->with('success', 'Transaction recorded successfully.');
    }

    public function voucher(MainBalance $mainBalance): View
    {
        if ($mainBalance->branch_id !== Auth::id()) {
            abort(403);
        }

        $mainBalance->load('user');

        $companyName = Setting::get('company_name', config('app.name'));

        return view('balance-voucher', compact('mainBalance', 'companyName'));
    }
}
