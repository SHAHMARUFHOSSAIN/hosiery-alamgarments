@extends('layouts.admin')

@section('title', __('Product List'))

@section('header', __('Product Management'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Products') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="mb-0">{{ __('Product Management') }}</h2>
        <div class="btn-group btn-group-sm mt-2" role="group" aria-label="Product navigation">
            <a href="{{ route('products.catalog') }}" class="btn btn-outline-primary active">
                <i class="bi bi-list-ul"></i> {{ __('Product List') }}
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up"></i> {{ __('Sales Reports') }}
            </a>
        </div>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProductModal">
        <i class="bi bi-plus"></i> {{ __('New Product') }}
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="{{ __('Search product name or unit...') }}"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('products.catalog') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Product Name') }}</th>
                    <th class="text-end">{{ __('Rate') }}</th>
                    <th>{{ __('Unit') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td class="text-end">{{ $product->rate !== null ? format_number($product->rate, 2) : '—' }}</td>
                    <td>{{ $product->unit ?? '—' }}</td>
                    <td>
                        @if($product->is_active)
                        <span class="badge bg-success">{{ __('Active') }}</span>
                        @else
                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                        @endif
                    </td>
                    <td>{{ $product->creator->name ?? 'N/A' }}</td>
                    <td>{{ $product->created_at->format('M d, Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                              class="d-inline" onsubmit="return confirm('{{ __("Delete this product?") }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-3">{{ __('No products found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $products->links() !!}
    </div>
    @endif
</div>

<div class="modal fade" id="newProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('New Product') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('products.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Rate') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" min="0" name="rate" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Unit') }}</label>
                        <input type="text" name="unit" class="form-control" placeholder="{{ __('e.g. pcs, dozen, kg') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Product') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($products as $product)
<div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Product') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Rate') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" min="0" name="rate" class="form-control"
                                   value="{{ $product->rate !== null ? number_format((float) $product->rate, 2, '.', '') : '' }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Unit') }}</label>
                        <input type="text" name="unit" class="form-control" value="{{ $product->unit }}">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $product->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update Product') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection