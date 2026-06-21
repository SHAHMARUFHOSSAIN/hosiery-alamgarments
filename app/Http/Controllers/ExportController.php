<?php

namespace App\Http\Controllers;

use App\Exports\BillsExport;
use App\Exports\CustomersExport;
use App\Exports\DuesExport;
use App\Exports\InactiveCustomersExport;
use App\Exports\PreviousDuesExport;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\PreviousDue;
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
                $query->whereDate('report_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('report_date', '<=', $request->date_to);
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

    public function customer(Request $request, Customer $customer)
    {
        if (!Auth::user()->isAdmin() && $customer->created_by !== Auth::id()) {
            abort(403);
        }

        $customer->load(['creator']);

        $billQuery = $customer->bills()->with('payments');

        if ($request->filled('date_from')) {
            $billQuery->whereDate('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $billQuery->whereDate('report_date', '<=', $request->date_to);
        }
        if (Auth::user()->isAdmin() && $request->filled('user_id')) {
            $billQuery->where('user_id', $request->user_id);
        }

        $bills = $billQuery->latest()->get();

        $prevDueQuery = $customer->previousDues();
        if ($request->filled('date_from')) {
            $prevDueQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $prevDueQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $prevDues = $prevDueQuery->latest()->get();

        $rows = collect();

        foreach ($bills as $bill) {
            $payment = $bill->payments->first();
            $rows->push([
                'Section' => 'Bill',
                'Customer ID' => $customer->id,
                'Customer' => $customer->name,
                'Mobile' => $customer->mobile ?? 'N/A',
                'Bill No' => $bill->bill_no,
                'Shop' => $bill->shop_name ?? 'N/A',
                'Bill Man' => $bill->bill_man ?? 'N/A',
                'Amount' => number_format($bill->bill_amount, 2),
                'Discount' => number_format($bill->discount ?? 0, 2),
                'Net' => number_format($bill->bill_amount - ($bill->discount ?? 0), 2),
                'Payment Type' => $payment ? ucfirst($payment->payment_type) : 'N/A',
                'Payment Status' => $payment ? ucfirst($payment->status) : 'N/A',
                'Bank' => $payment ? ($payment->bank_name ?? 'N/A') : 'N/A',
                'Check No' => $payment ? ($payment->check_no ?? 'N/A') : 'N/A',
                'Check Amount' => $payment ? number_format($payment->check_amount ?? 0, 2) : 'N/A',
                'Check Date' => $payment && $payment->check_date ? $payment->check_date->format('Y-m-d') : 'N/A',
                'TT Bank' => $payment && $payment->payment_type === 'tt' ? ($payment->tt_bank_name ?? 'N/A') : 'N/A',
                'TT Amount' => $payment && $payment->payment_type === 'tt' ? number_format($payment->tt_amount ?? 0, 2) : 'N/A',
                'Card Ref' => $payment && $payment->payment_type === 'card' ? ($payment->card_reference ?? 'N/A') : 'N/A',
                'Card Amount' => $payment && $payment->payment_type === 'card' ? number_format($payment->card_amount ?? 0, 2) : 'N/A',
                'Due Date' => $payment && $payment->due_date ? $payment->due_date->format('Y-m-d') : 'N/A',
                'Date' => $bill->report_date?->format('Y-m-d') ?? 'N/A',
            ]);
        }

        foreach ($prevDues as $pd) {
            $rows->push([
                'Section' => 'Previous Due',
                'Customer ID' => $customer->id,
                'Customer' => $customer->name,
                'Mobile' => $customer->mobile ?? 'N/A',
                'Bill No' => 'N/A',
                'Shop' => 'N/A',
                'Bill Man' => 'N/A',
                'Amount' => number_format($pd->original_amount, 2),
                'Discount' => 'N/A',
                'Net' => 'N/A',
                'Payment Type' => 'N/A',
                'Payment Status' => ucfirst($pd->status),
                'Bank' => 'N/A',
                'Check No' => 'N/A',
                'Check Amount' => 'N/A',
                'Check Date' => 'N/A',
                'TT Bank' => 'N/A',
                'TT Amount' => 'N/A',
                'Card Ref' => 'N/A',
                'Card Amount' => 'N/A',
                'Due Date' => 'N/A',
                'Date' => $pd->created_at?->format('Y-m-d') ?? 'N/A',
            ]);
        }

        if ($rows->isEmpty()) {
            $rows->push([
                'Section' => 'No Data',
                'Customer ID' => $customer->id,
                'Customer' => $customer->name,
                'Mobile' => $customer->mobile ?? 'N/A',
                'Bill No' => 'N/A', 'Shop' => 'N/A', 'Bill Man' => 'N/A',
                'Amount' => 'N/A', 'Discount' => 'N/A', 'Net' => 'N/A',
                'Payment Type' => 'N/A', 'Payment Status' => 'N/A',
                'Bank' => 'N/A', 'Check No' => 'N/A', 'Check Amount' => 'N/A', 'Check Date' => 'N/A',
                'TT Bank' => 'N/A', 'TT Amount' => 'N/A',
                'Card Ref' => 'N/A', 'Card Amount' => 'N/A',
                'Due Date' => 'N/A', 'Date' => 'N/A',
            ]);
        }

        $headings = array_keys($rows->first());

        return Excel::download(
            new class($rows, $headings) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                protected $rows;
                protected $headings;

                public function __construct($rows, $headings)
                {
                    $this->rows = $rows;
                    $this->headings = $headings;
                }

                public function collection()
                {
                    return $this->rows;
                }

                public function headings(): array
                {
                    return $this->headings;
                }
            },
            'customer_' . $customer->id . '_' . date('Ymd') . '.xlsx'
        );
    }

    public function customers(Request $request)
    {
        $query = Customer::with(['creator', 'previousDues']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('created_by', $request->user_id);
            }
        } else {
            $query->where('created_by', Auth::id());
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

        $customers = $query->orderBy('id', 'desc')->get();

        return Excel::download(new CustomersExport($customers), 'customers_' . date('Ymd') . '.xlsx');
    }

    public function previousDues(Request $request)
    {
        $query = PreviousDue::with(['customer', 'creator']);

        if (Auth::user()->isAdmin()) {
            if ($request->filled('user_id')) {
                $query->where('created_by', $request->user_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('status')) {
                if ($request->status === 'partial') {
                    $query->where('status', 'pending')->whereHas('payments');
                } else {
                    $query->where('status', $request->status);
                }
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            }
        } else {
            $query->where('created_by', Auth::id());
        }

        $previousDues = $query->latest()->get();

        return Excel::download(new PreviousDuesExport($previousDues), 'previous_dues_' . date('Ymd') . '.xlsx');
    }

    public function test()
    {
        return response()->json(['status' => 'ok']);
    }
}