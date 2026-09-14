<?php

namespace App\Http\Controllers;

use App\Models\BillProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function catalog(Request $request): View
    {
        $query = Product::with('creator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('id', 'desc')->paginate(15);

        return view('products.catalog', compact('products'));
    }

    public function index(Request $request): View
    {
        $period = $request->get('period', 'all');
        $allowedPeriods = ['today', 'week', 'month', 'year', 'all'];
        if (!in_array($period, $allowedPeriods)) {
            $period = 'all';
        }

        $topProductsQuery = BillProduct::selectRaw('product_name, SUM(quantity) as total_qty, SUM(price) as total_amount')
            ->groupBy('product_name');

        if ($period !== 'all') {
            $ranges = [
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'week' => [now()->startOfWeek(), now()->endOfWeek()],
                'month' => [now()->startOfMonth(), now()->endOfMonth()],
                'year' => [now()->startOfYear(), now()->endOfYear()],
            ];
            $topProductsQuery->join('bills', 'bill_products.bill_id', '=', 'bills.id')
                ->whereBetween('bills.report_date', $ranges[$period]);
        }

        $topProducts = $topProductsQuery->orderByDesc('total_qty')->take(10)->get();

        $userWise = BillProduct::join('bills', 'bill_products.bill_id', '=', 'bills.id')
            ->join('users', 'bills.user_id', '=', 'users.id')
            ->selectRaw('users.id as user_id, users.name as user_name, SUM(bill_products.quantity) as total_qty, SUM(bill_products.price) as total_amount, COUNT(DISTINCT bills.id) as bill_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_qty')
            ->get();

        return view('products.index', compact('topProducts', 'userWise', 'period'));
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->get('term', '');

        $products = Product::where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->select('id', 'name', 'rate', 'unit')
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
        ]);

        Product::create([
            'name' => $validated['name'],
            'rate' => $validated['rate'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('products.catalog')->with('success', 'Product created successfully');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $product->update([
            'name' => $validated['name'],
            'rate' => $validated['rate'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('products.catalog')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.catalog')->with('success', 'Product deleted successfully');
    }
}