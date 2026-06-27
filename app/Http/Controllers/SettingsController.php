<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Bill;
use App\Models\Due;
use App\Models\MainBalance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $days = $request->get('days', 7);
        $startDate = now()->subDays($days)->startOfDay();
        
        $stats = [
            'totalUsers' => User::count(),
            'totalCustomers' => Customer::count(),
            'totalBills' => Bill::count(),
            'totalDues' => Due::where('status', 'pending')->count(),
            'totalRevenue' => MainBalance::where('type', 'credit')->sum('amount'),
            'recentUsers' => User::where('created_at', '>=', $startDate)->count(),
            'recentCustomers' => Customer::where('created_at', '>=', $startDate)->count(),
            'recentBills' => Bill::where('report_date', '>=', $startDate)->count(),
            'recentRevenue' => MainBalance::where('type', 'credit')->where('created_at', '>=', $startDate)->sum('amount'),
        ];

        return view('settings.index', compact('stats', 'days'));
    }

    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);

        return view('settings.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('settings.users')->with('success', 'User created successfully');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.users')->with('success', 'User updated successfully');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('settings.users')->with('error', 'You cannot delete yourself');
        }

        $user->delete();

        return redirect()->route('settings.users')->with('success', 'User deleted successfully');
    }

    public function systemInfo(Request $request): View
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        
        $stats = [
            'totalUsers' => User::count(),
            'totalCustomers' => Customer::count(),
            'totalBills' => Bill::count(),
            'totalDues' => Due::count(),
            'pendingDues' => Due::where('status', 'pending')->count(),
            'paidDues' => Due::where('status', 'paid')->count(),
            'totalCredit' => MainBalance::where('type', 'credit')->sum('amount'),
            'totalDebit' => MainBalance::where('type', 'debit')->sum('amount'),
            'recentUsers' => User::where('created_at', '>=', $startDate)->count(),
            'recentCustomers' => Customer::where('created_at', '>=', $startDate)->count(),
            'recentBills' => Bill::where('report_date', '>=', $startDate)->count(),
            'recentDues' => Due::where('created_at', '>=', $startDate)->count(),
        ];

        return view('settings.system-info', compact('stats', 'days'));
    }

    public function dataManagement(Request $request): View
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        
        $recentBills = Bill::with(['customer', 'user', 'editor'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('id', 'desc')
            ->paginate(15);
            
        $recentCustomers = Customer::with('creator')
            ->where('created_at', '>=', $startDate)
            ->orderBy('id', 'desc')
            ->paginate(15);
            
        $recentDues = Due::with(['customer', 'bill'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $recentBanks = \App\Models\Bank::with('creator')
            ->where('created_at', '>=', $startDate)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $recentChecks = \App\Models\Payment::with(['bill.customer'])
            ->where('payment_type', 'check')
            ->where('created_at', '>=', $startDate)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('settings.data-management', compact('recentBills', 'recentCustomers', 'recentDues', 'recentBanks', 'recentChecks', 'days'));
    }

    public function deleteBill(Bill $bill)
    {
        $billNumber = $bill->bill_no;
        $bill->delete();
        
        return redirect()->route('settings.data')->with('success', "Bill {$billNumber} deleted successfully");
    }

    public function deleteCustomer(Customer $customer)
    {
        $customerName = $customer->name;
        $customer->delete();
        
        return redirect()->route('settings.data')->with('success', "Customer {$customerName} deleted successfully");
    }

    public function deleteDue(Due $due)
    {
        $due->delete();
        
        return redirect()->route('settings.data')->with('success', 'Due deleted successfully');
    }

    public function editBill(Request $request, Bill $bill)
    {
        $bill->update([
            'bill_no' => $request->bill_no,
            'shop_name' => $request->shop_name,
            'bill_man' => $request->bill_man,
            'bill_amount' => $request->bill_amount,
            'discount' => $request->discount,
            'report_date' => $request->report_date,
            'edited_at' => now(),
            'edited_by' => Auth::id(),
        ]);
        return redirect()->route('settings.data')->with('success', 'Bill updated successfully');
    }

    public function editCustomer(Request $request, Customer $customer)
    {
        $customer->update($request->only(['name', 'mobile', 'location']));
        return redirect()->route('settings.data')->with('success', 'Customer updated successfully');
    }

    public function editDue(Request $request, Due $due)
    {
        $due->update($request->only(['amount', 'due_date', 'status']));
        return redirect()->route('settings.data')->with('success', 'Due updated successfully');
    }

    public function company(): View
    {
        $settings = [
            'company_name' => Setting::get('company_name', config('app.name')),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_email' => Setting::get('company_email', ''),
            'voucher_prefix' => Setting::get('voucher_prefix', 'V'),
            'company_logo' => Setting::get('company_logo'),
            'company_favicon' => Setting::get('company_favicon'),
        ];

        return view('settings.company', compact('settings'));
    }

    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'voucher_prefix' => 'nullable|string|max:10',
            'company_logo' => 'nullable|image|max:2048',
            'company_favicon' => 'nullable|image|max:1024',
        ]);

        foreach ($validated as $key => $value) {
            if (in_array($key, ['company_logo', 'company_favicon'])) {
                continue;
            }
            Setting::set($key, $value);
        }

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company', 'public');
            Setting::set('company_logo', $path);
        }

        if ($request->hasFile('company_favicon')) {
            $path = $request->file('company_favicon')->store('company', 'public');
            Setting::set('company_favicon', $path);
        }

        return redirect()->route('settings.company')->with('success', 'Company settings updated successfully.');
    }
}