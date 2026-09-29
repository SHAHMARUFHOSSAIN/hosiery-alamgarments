<?php

namespace App\Http\Controllers;

use App\Models\BillProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
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
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && array_key_exists($request->category, Product::categories())) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('id', 'desc')->paginate(30)->withQueryString();

        return view('products.catalog', compact('products'));
    }

    public function index(Request $request): View
    {
        $period = $request->get('period', 'all');
        $allowedPeriods = ['today', 'week', 'month', 'year', 'all'];
        if (!in_array($period, $allowedPeriods)) {
            $period = 'all';
        }

        $category = $request->get('category', '');
        $category = array_key_exists($category, Product::categories()) ? $category : '';

        $topProductsQuery = $this->resolvedProductLines()
            ->select('bp.product_name', 'bp.category', 'bp.size_name')
            ->selectRaw('SUM(bp.quantity) as total_qty, SUM(bp.price) as total_amount')
            ->groupBy('bp.product_name', 'bp.category', 'bp.size_name');

        $categoryWiseQuery = $this->resolvedProductLines()
            ->select('bp.category')
            ->selectRaw('COUNT(DISTINCT bp.product_name) as product_count')
            ->selectRaw('SUM(bp.quantity) as total_qty, SUM(bp.price) as total_amount, COUNT(DISTINCT bp.bill_id) as bill_count')
            ->groupBy('bp.category');

        $userWiseQuery = $this->resolvedProductLines()
            ->join('users', 'bills.user_id', '=', 'users.id')
            ->selectRaw('users.id as user_id, users.name as user_name, SUM(bp.quantity) as total_qty, SUM(bp.price) as total_amount, COUNT(DISTINCT bills.id) as bill_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_qty');

        if ($period !== 'all') {
            $ranges = [
                'today' => [now()->startOfDay(), now()->endOfDay()],
                'week' => [now()->startOfWeek(), now()->endOfWeek()],
                'month' => [now()->startOfMonth(), now()->endOfMonth()],
                'year' => [now()->startOfYear(), now()->endOfYear()],
            ];

            $topProductsQuery->whereBetween('bills.report_date', $ranges[$period]);
            $categoryWiseQuery->whereBetween('bills.report_date', $ranges[$period]);
            $userWiseQuery->whereBetween('bills.report_date', $ranges[$period]);
        }

        if ($category !== '') {
            $topProductsQuery->where('bp.category', $category);
            $categoryWiseQuery->where('bp.category', $category);
            $userWiseQuery->where('bp.category', $category);
        }

        $topProducts = $topProductsQuery->orderByDesc('total_qty')->take(10)->get();
        $categoryWise = $categoryWiseQuery->orderByDesc('total_amount')->get();
        $userWise = $userWiseQuery->get();

        return view('products.index', compact('topProducts', 'categoryWise', 'userWise', 'period', 'category'));
    }

    /**
     * Bill product lines with size/category resolved, falling back
     * to the product catalog for lines saved before those columns existed.
     */
    private function resolvedProductLines(): Builder
    {
        $lines = BillProduct::query()
            ->select('bill_products.id', 'bill_products.bill_id', 'bill_products.product_name', 'bill_products.quantity', 'bill_products.price')
            ->selectRaw(BillProduct::resolvedCategorySql() . ' as category')
            ->selectRaw(BillProduct::resolvedSizeSql() . ' as size_name');

        return BillProduct::query()
            ->fromSub($lines, 'bp')
            ->join('bills', 'bp.bill_id', '=', 'bills.id');
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->get('term', '');

        $products = Product::where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->select('id', 'category', 'name', 'size', 'rate', 'unit')
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $category = $this->normalize($request->input('category'));
        $size = $this->normalize($request->input('size'));

        $validated = $request->validate([
            'category' => $this->categoryRule(),
            'name' => ['required', 'string', 'max:255', $this->nameUniqueRule($category, $size)],
            'size' => 'nullable|string|max:100',
            'rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
        ]);

        Product::create([
            'category' => $category !== '' ? $category : null,
            'name' => $validated['name'],
            'size' => $size !== '' ? $size : null,
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
        $category = $this->normalize($request->input('category'));
        $size = $this->normalize($request->input('size'));

        $validated = $request->validate([
            'category' => $this->categoryRule(),
            'name' => ['required', 'string', 'max:255', $this->nameUniqueRule($category, $size, $product->id)],
            'size' => 'nullable|string|max:100',
            'rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $product->update([
            'category' => $category !== '' ? $category : null,
            'name' => $validated['name'],
            'size' => $size !== '' ? $size : null,
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

    private function normalize($value): string
    {
        return trim((string) $value);
    }

    private function categoryRule(): string
    {
        return 'nullable|string|max:50|in:' . implode(',', array_keys(Product::categories()));
    }

    private function nameUniqueRule(string $category, string $size, ?int $ignoreId = null): Unique
    {
        $rule = Rule::unique('products', 'name')->where(function ($query) use ($category, $size) {
            $query->whereRaw('COALESCE(category, "") = ?', [$category])
                  ->whereRaw('COALESCE(size, "") = ?', [$size]);
        });

        return $ignoreId ? $rule->ignore($ignoreId) : $rule;
    }
}
