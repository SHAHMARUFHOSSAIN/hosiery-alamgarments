<?php

namespace App\Http\Controllers;

use App\Exports\BillsExport;
use App\Exports\DuesExport;
use App\Exports\InactiveCustomersExport;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function bills(Request $request)
    {
        $query = Bill::with(['customer', 'user']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('bill_no', 'like', "%{$search}%")
                      ->orWhere('shop_name', 'like', "%{$search}%")
                      ->orWhere('bill_man', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%")
                             ->orWhere('mobile', 'like', "%{$search}%");
                      });
                });
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        $bills = $query->orderBy('id', 'desc')->get();

        return Excel::download(new BillsExport($bills), 'bills_' . date('Ymd') . '.xlsx');
    }

    public function dues(Request $request)
    {
        $query = Due::with(['customer', 'bill', 'creator']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('created_by', $request->user_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('due_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('due_date', '<=', $request->date_to);
            }
            if ($request->filled('status')) {
                if ($request->status === 'partial') {
                    $query->where('status', 'pending')->whereHas('duePayments');
                } else {
                    $query->where('status', $request->status);
                }
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                           ->orWhere('mobile', 'like', "%{$search}%");
                    })->orWhereHas('bill', function ($bq) use ($search) {
                        $bq->where('bill_no', 'like', "%{$search}%");
                    });
                });
            }
        } else {
            $query->where('created_by', Auth::id());
        }

        $dues = $query->orderBy('due_date', 'asc')->get();

        return Excel::download(new DuesExport($dues), 'dues_' . date('Ymd') . '.xlsx');
    }

    public function inactiveCustomers(Request $request)
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

        $customers = $query->orderBy('id', 'desc')->get();

        return Excel::download(new InactiveCustomersExport($customers), 'inactive_customers_' . date('Ymd') . '.xlsx');
    }

    public function test()
    {
        return response()->json(['status' => 'ok']);
    }
}