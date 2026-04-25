<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $users = User::where('role', 'user')->get(['id', 'name']);

        $query = Bill::query();
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }
        $totalSales = $query->sum('bill_amount');

        $dueQuery = Due::query();
        if (!Auth::user()->isAdmin()) {
            $dueQuery->where('created_by', Auth::id());
        }
        $totalDues = $dueQuery->where('status', 'pending')->sum('amount');
        $paidDues = $dueQuery->where('status', 'paid')->sum('amount');

        return view('reports.index', compact('users', 'totalSales', 'totalDues', 'paidDues'));
    }

    public function sales(Request $request): View
    {
        $query = Bill::with(['customer', 'user']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $bills = $query->orderBy('id', 'desc')->paginate(20);
        $totalAmount = $query->sum('bill_amount');
        $totalDiscount = $query->sum('discount');
        $users = User::where('role', 'user')->get(['id', 'name']);

        return view('reports.sales', compact('bills', 'totalAmount', 'totalDiscount', 'users'));
    }

    public function dues(Request $request): View
    {
        $query = Due::with(['customer', 'bill', 'creator']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('created_by', $request->user_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        $dues = $query->orderBy('due_date', 'asc')->paginate(20);
        $totalAmount = $query->where('status', 'pending')->sum('amount');
        $users = User::where('role', 'user')->get(['id', 'name']);

        return view('reports.dues', compact('dues', 'totalAmount', 'users'));
    }

    public function inactiveCustomers(Request $request): View
    {
        $query = Customer::with('creator')->where('is_active', false);

        if (!Auth::user()->isAdmin()) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('id', 'desc')->paginate(20);
        $customers->appends($request->only('search'));

        return view('reports.inactive-customers', compact('customers'));
    }
}