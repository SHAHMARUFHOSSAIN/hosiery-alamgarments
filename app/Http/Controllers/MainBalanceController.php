<?php

namespace App\Http\Controllers;

use App\Helpers\VoucherHelper;
use App\Models\MainBalance;
use App\Models\Setting;
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

        $balances = $query->orderBy('id', 'desc')->paginate(20);

        $totalCredit = MainBalance::where('type', 'credit')
            ->when(Auth::user()->isAdmin() && $request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->unless(Auth::user()->isAdmin(), fn($q) => $q->where('branch_id', Auth::id()))
            ->sum('amount');
        $totalDebit = MainBalance::where('type', 'debit')
            ->when(Auth::user()->isAdmin() && $request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->unless(Auth::user()->isAdmin(), fn($q) => $q->where('branch_id', Auth::id()))
            ->sum('amount');

        $mainBalance = $totalCredit - $totalDebit;

        $allCredit = MainBalance::where('type', 'credit')
            ->unless(Auth::user()->isAdmin(), fn($q) => $q->where('branch_id', Auth::id()))
            ->sum('amount');
        $allDebit = MainBalance::where('type', 'debit')
            ->unless(Auth::user()->isAdmin(), fn($q) => $q->where('branch_id', Auth::id()))
            ->sum('amount');
        $overallBalance = $allCredit - $allDebit;

        $branches = Auth::user()->isAdmin() ? User::where('role', 'user')->get(['id', 'name']) : collect();

        $selectedBranch = $request->filled('branch_id') ? User::find($request->branch_id) : null;

        $userWiseBalance = [];
        if (Auth::user()->isAdmin()) {
            foreach ($branches as $branch) {
                $credit = MainBalance::where('branch_id', $branch->id)->where('type', 'credit')->sum('amount');
                $debit = MainBalance::where('branch_id', $branch->id)->where('type', 'debit')->sum('amount');
                $userWiseBalance[$branch->id] = [
                    'name' => $branch->name,
                    'credit' => $credit,
                    'debit' => $debit,
                    'balance' => $credit - $debit,
                ];
            }
        }

        return view('main-balance.index', compact(
            'balances', 'mainBalance', 'totalCredit', 'totalDebit',
            'allCredit', 'allDebit', 'overallBalance',
            'branches', 'selectedBranch', 'userWiseBalance'
        ));
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

        $branchId = Auth::user()->isAdmin() ? ($request->branch_id ?? Auth::id()) : Auth::id();

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

        return redirect()->route('main-balance.index', $request->only('branch_id'))
            ->with('success', 'Transaction recorded successfully.');
    }

    public function balanceReport(Request $request): View
    {
        $query = MainBalance::with(['user', 'branch']);

        if (!Auth::user()->isAdmin()) {
            $query->where('branch_id', Auth::id());
        } else if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $balances = $query->orderBy('id', 'desc')->paginate(20);

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

        $base = MainBalance::query();
        if (!Auth::user()->isAdmin()) {
            $base->where('branch_id', Auth::id());
        } elseif ($request->filled('branch_id')) {
            $base->where('branch_id', $request->branch_id);
        }
        $totalCredit = (clone $base)->where('type', 'credit')->sum('amount');
        $totalDebit = (clone $base)->where('type', 'debit')->sum('amount');
        $totalMainBalance = $totalCredit - $totalDebit;

        return view('main-balance.report', compact('balances', 'branchWise', 'totalMainBalance', 'totalCredit', 'totalDebit', 'branches'));
    }

    public function voucher(MainBalance $mainBalance): View
    {
        if (!Auth::user()->isAdmin() && $mainBalance->branch_id !== Auth::id()) {
            abort(403);
        }

        $mainBalance->load('user');

        $companyName = Setting::get('company_name', config('app.name'));

        return view('balance-voucher', compact('mainBalance', 'companyName'));
    }
}
