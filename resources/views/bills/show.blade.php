@extends('layouts.admin')

@section('title', __('Bill') . ': ' . $bill->bill_no)

@section('header', __('Bill Details'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">{{ __('Bills') }}</a></li>
        <li class="breadcrumb-item active">{{ $bill->bill_no }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('Bill') }}: {{ $bill->bill_no }}
        @if($bill->edited_at)
            <span class="badge bg-warning text-dark fs-6 align-middle" title="{{ __('Edited by') }} {{ $bill->editor?->name ?? __('Unknown') }} {{ __('on') }} {{ $bill->edited_at->format('M d, Y h:i A') }}">{{ __('Edited') }}</span>
        @endif
    </h2>
    <div>
        <a href="{{ route('bills.edit', ['bill' => $bill, 'page' => request('page', 1)]) }}" class="btn btn-secondary">
            <i class="bi bi-pencil"></i> {{ __('Edit') }}
        </a>
        <a href="{{ route('bills.index', ['page' => request('page', 1), 'search' => request('search'), 'user_id' => request('user_id'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Bill Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-borderless">
                    <tr><th>{{ __('Bill No:') }}</th><td>{{ $bill->bill_no }}</td></tr>
                    <tr><th>{{ __('Customer:') }}</th><td><a href="{{ route('customers.show', $bill->customer) }}">{{ $bill->customer->name ?? __('N/A') }}</a></td></tr>
                    <tr><th>{{ __('Shop:') }}</th><td>{{ $bill->shop_name ?? __('N/A') }}</td></tr>
                    <tr><th>{{ __('Bill Man:') }}</th><td>{{ $bill->bill_man ?? __('N/A') }}</td></tr>
                    <tr><th>{{ __('Amount:') }}</th><td class="fw-bold">{{ format_number($bill->bill_amount, 2) }}</td></tr>
                    <tr><th>{{ __('Discount:') }}</th><td>{{ format_number($bill->discount, 2) }}</td></tr>
                    <tr><th>{{ __('Net:') }}</th><td class="fw-bold text-success">{{ format_number($bill->bill_amount - $bill->discount, 2) }}</td></tr>
                    <tr><th>{{ __('User:') }}</th><td><span class="badge bg-secondary">{{ $bill->user->name ?? __('N/A') }}</span></td></tr>
                    <tr><th>{{ __('Date:') }}</th><td>{{ $bill->report_date?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</td></tr>
                    @if($bill->edited_at)
                    <tr><th>{{ __('Edited:') }}</th><td><span class="badge bg-warning text-dark">{{ __('Yes') }}</span> <small class="text-muted">{{ __('by') }} {{ $bill->editor?->name ?? __('Unknown') }} {{ __('on') }} {{ $bill->edited_at->format('M d, Y h:i A') }}</small></td></tr>
                    @endif
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> {{ __('Product Details') }}</h5>
                <button type="button" class="btn btn-sm btn-primary" id="addProductBtn">
                    <i class="bi bi-plus"></i> {{ __('Add Product') }}
                </button>
            </div>
            <form method="POST" action="{{ route('bills.products.update', $bill) }}" id="billProductsForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @error('products')
                    <div class="alert alert-danger py-1 mb-2"><small><i class="bi bi-exclamation-triangle"></i> {{ $message }}</small></div>
                    @enderror
                    <small class="text-muted d-block mb-3">{{ __('Add or update the items of this bill. The item total must match the bill amount.') }} <strong>{{ format_currency($bill->bill_amount) }}</strong></small>
                    <datalist id="catalogProducts">
                        @foreach($products as $product)
                        <option value="{{ $product->name }}" data-rate="{{ $product->rate }}" data-unit="{{ $product->unit }}">{{ $product->unit ? __('Rate') . ': ' . format_number($product->rate, 2) . ' / ' . $product->unit : '' }}</option>
                        @endforeach
                    </datalist>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-2 product-items-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 41%;">{{ __('Product Name') }}</th>
                                    <th style="width: 14%;">{{ __('Rate (৳)') }}</th>
                                    <th style="width: 14%;">{{ __('Quantity') }}</th>
                                    <th style="width: 16%;" class="text-end">{{ __('Price (৳)') }}</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="productsContainer">
                                @php $productIndex = 0; @endphp
                                @forelse($bill->billProducts as $bp)
                                <tr class="product-item" data-index="{{ $productIndex }}">
                                    <td class="product-sno"></td>
                                    <td><input type="text" name="products[{{ $productIndex }}][product_name]" class="form-control form-control-sm product-name-input" placeholder="{{ __('e.g. Round Neck T-Shirt') }}" value="{{ old('products.' . $productIndex . '.product_name', $bp->product_name) }}" autocomplete="off" list="catalogProducts"></td>
                                    <td><input type="number" step="0.01" min="0" name="products[{{ $productIndex }}][rate]" class="form-control form-control-sm product-rate-input" value="{{ old('products.' . $productIndex . '.rate', number_format($bp->rate, 2, '.', '')) }}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.01" min="0" name="products[{{ $productIndex }}][quantity]" class="form-control form-control-sm product-qty-input" value="{{ old('products.' . $productIndex . '.quantity', number_format($bp->quantity, 2, '.', '')) }}" placeholder="0"></td>
                                    <td class="text-end product-price fw-semibold">{{ old('products.' . $productIndex . '.price', number_format($bp->price, 2, '.', '')) }}</td>
                                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-product-btn" title="{{ __('Remove') }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @php $productIndex++; @endphp
                                @empty
                                <tr class="product-item" data-index="0">
                                    <td class="product-sno"></td>
                                    <td><input type="text" name="products[0][product_name]" class="form-control form-control-sm product-name-input" placeholder="{{ __('e.g. Round Neck T-Shirt') }}" autocomplete="off" list="catalogProducts"></td>
                                    <td><input type="number" step="0.01" min="0" name="products[0][rate]" class="form-control form-control-sm product-rate-input" placeholder="0.00"></td>
                                    <td><input type="number" step="0.01" min="0" name="products[0][quantity]" class="form-control form-control-sm product-qty-input" placeholder="0"></td>
                                    <td class="text-end product-price fw-semibold">0.00</td>
                                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-product-btn" style="display: none;" title="{{ __('Remove') }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">{{ __('Total') }}:</td>
                                    <td class="text-end" id="productsTotal">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="productsMatchAlert" class="alert alert-warning d-none py-2 mb-2">
                        <i class="bi bi-exclamation-triangle"></i> <span id="productsMatchMessage"></span>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> {{ __('Save Products') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Payments') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Details') }}</th><th>{{ __('Due Date') }}</th><th>{{ __('Date') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bill->payments as $payment)
                        <tr>
                            <td><span class="badge bg-{{ $payment->payment_type === 'due' ? 'danger' : ($payment->payment_type === 'check' ? 'warning text-dark' : ($payment->payment_type === 'tt' ? 'info text-dark' : ($payment->payment_type === 'card' ? 'secondary text-white' : 'primary'))) }}">
                                {{ $payment->payment_type === 'check' ? __('CHEQUE') : ($payment->payment_type === 'card' ? __('REFERENCE CARD') : strtoupper($payment->payment_type)) }}
                            </span></td>
                            <td>{{ format_number($payment->amount, 2) }}</td>
                            <td>{{ $payment->details ?? __('N/A') }}</td>
                            <td>{{ $payment->due_date?->format('M d, Y') ?? __('N/A') }}</td>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                        </tr>
                        @if($payment->payment_type === 'check')
                        <tr class="table-warning">
                            <td colspan="5">
                                <div class="row g-2">
                                    <div class="col-md-2"><strong>{{ __('Bank:') }}</strong> {{ $payment->bank_name ?? __('N/A') }}</div>
                                    <div class="col-md-2"><strong>{{ __('Cheque No:') }}</strong> {{ $payment->check_no ?? __('N/A') }}</div>
                                    <div class="col-md-2"><strong>{{ __('Cheque Amt:') }}</strong> {{ format_number($payment->check_amount, 2) }}</div>
                                    <div class="col-md-2"><strong>{{ __('Cheque Date:') }}</strong> {{ $payment->check_date?->format('M d, Y') ?? __('N/A') }}</div>
                                    <div class="col-md-2"><strong>{{ __('Reminder:') }}</strong> {{ $payment->check_reminder_date?->format('M d, Y') ?? __('N/A') }}</div>
                                    <div class="col-md-2"><strong>{{ __('Status:') }}</strong> 
                                        @if($payment->status === 'encashed')
                                        <span class="badge bg-success">{{ __('Encashed') }}</span>
                                        @else
                                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                        @endif
                                    </div>
                                    @if($payment->status === 'encashed' && $payment->checkEncashments->isNotEmpty())
                                    <div class="col-md-2"><strong>{{ __('TXN ID:') }}</strong> {{ $payment->checkEncashments->first()->transaction_id ?? __('N/A') }}</div>
                                    @endif
                                    @if($payment->check_photo)
                                    <div class="col-12">
                                        <a href="{{ route('cheque.show', $payment->check_photo) }}" target="_blank">
                                             <img src="{{ route('cheque.show', $payment->check_photo) }}" alt="{{ __('Cheque') }}" class="img-fluid border rounded" style="max-height: 120px;">
                                        </a>
                                    </div>
                                    @endif
                                    @if($payment->status === 'pending' && $payment->check_amount > 0)
                                    <div class="col-12">
                                        <form method="POST" action="{{ route('dues.encash', $payment) }}" class="d-inline"
                                              onsubmit="return confirm('{{ __("Encash") }} {{ format_currency($payment->remainingCheckAmount()) }}?')">
                                            @csrf
                                            <input type="hidden" name="encash_amount" value="{{ $payment->remainingCheckAmount() }}">
                                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                                <select name="payment_type" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="cash">{{ __('Cash') }}</option>
                                                    <option value="check">{{ __('Cheque') }}</option>
                                                    <option value="mobile_banking">{{ __('Mobile Banking') }}</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-circle"></i> {{ __('Encash Cheque') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($payment->payment_type === 'tt')
                        <tr class="table-info">
                            <td colspan="5">
                                <div class="row g-2">
                                    <div class="col-md-3"><strong>{{ __('Bank:') }}</strong> {{ $payment->tt_bank_name ?? __('N/A') }}</div>
                                    <div class="col-md-3"><strong>{{ __('Account No:') }}</strong> {{ $payment->tt_account_no ?? __('N/A') }}</div>
                                    <div class="col-md-3"><strong>{{ __('TT Amt:') }}</strong> {{ format_number($payment->tt_amount, 2) }}</div>
                                    <div class="col-md-3"><strong>{{ __('TT Date:') }}</strong> {{ $payment->tt_date?->format('M d, Y') ?? __('N/A') }}</div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($payment->payment_type === 'card')
                        <tr class="table-secondary">
                            <td colspan="5">
                                <div class="row g-2">
                                    <div class="col-md-3"><strong>{{ __('Reference Card:') }}</strong> {{ $payment->card_reference ?? __('N/A') }}</div>
                                    <div class="col-md-3"><strong>{{ __('Location:') }}</strong> {{ $payment->card_location ?? __('N/A') }}</div>
                                    <div class="col-md-3"><strong>{{ __('Card Amt:') }}</strong> {{ format_number($payment->card_amount, 2) }}</div>
                                    <div class="col-md-3"><strong>{{ __('Card Date:') }}</strong> {{ $payment->card_date?->format('M d, Y') ?? __('N/A') }}</div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr><td colspan="5" class="text-center py-3">{{ __('No payments found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Dues') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Original') }}</th>
                            <th>{{ __('Paid') }}</th>
                            <th>{{ __('Remaining') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bill->dues as $due)
                        <tr>
                            <td>{{ format_currency($due->original_amount) }}</td>
                            <td class="text-success fw-bold">{{ format_currency($due->total_paid) }}</td>
                            <td class="text-danger fw-bold">{{ format_currency($due->remaining_amount) }}</td>
                            <td><span class="badge bg-{{ $due->due_date->isPast() ? 'danger' : 'warning' }} text-dark">{{ $due->due_date->format('M d, Y') }}</span></td>
                            <td>
                                @if($due->status === 'paid')
                                <span class="badge bg-success">{{ __('Paid') }}</span>
                                @elseif($due->hasPartialPayments())
                                <span class="badge bg-info text-dark">{{ __('Partial') }}</span>
                                @else
                                <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($due->status == 'pending')
                                <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#billDuePayModal{{ $due->id }}">
                                    <i class="bi bi-credit-card"></i> {{ __('Pay') }}
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3">{{ __('No dues found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($bill->dues as $due)
@if($due->status === 'pending')
<div class="modal fade" id="billDuePayModal{{ $due->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Make Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>{{ __('Original Amount:') }}</strong> {{ format_currency($due->original_amount) }}
                    </div>
                    @if($due->hasPartialPayments())
                    <div class="mb-3">
                        <strong>{{ __('Total Paid:') }}</strong> <span class="text-success">{{ format_currency($due->total_paid) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>{{ __('Remaining:') }}</strong> <span class="text-danger fw-bold">{{ format_currency($due->remaining_amount) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" class="form-control" 
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="check">{{ __('Cheque') }}</option>
                            <option value="mobile_banking">{{ __('Mobile Banking') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Next Due Date') }} <small class="text-muted">({{ __('if remaining balance') }})</small></label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Transaction ID') }} <small class="text-muted">({{ __('for reference') }})</small></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="{{ __('e.g. TXN12345') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Note') }}</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="{{ __('Optional note...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> {{ __('Record Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection

@section('scripts')
<script>
(function() {
    'use strict';

    var billAmount = parseFloat('{{ $bill->bill_amount }}') || 0;
    var catalogProducts = JSON.parse('{!! json_encode($products) !!}'.replace(/&quot;/g, '"'));
    var productIndex = {{ $bill->billProducts->count() > 0 ? $bill->billProducts->count() : 1 }};
    var productsContainer = document.getElementById('productsContainer');
    var addProductBtn = document.getElementById('addProductBtn');

    function lookupCatalogProduct(name) {
        name = (name || '').trim().toLowerCase();
        if (!name) return null;
        for (var i = 0; i < catalogProducts.length; i++) {
            if ((catalogProducts[i].name || '').trim().toLowerCase() === name) {
                return catalogProducts[i];
            }
        }
        return null;
    }

    function updateProductRow(row) {
        if (!row) return;
        var rate = parseFloat(row.querySelector('.product-rate-input').value) || 0;
        var qty = parseFloat(row.querySelector('.product-qty-input').value) || 0;
        var priceEl = row.querySelector('.product-price');
        if (priceEl) priceEl.textContent = (rate * qty).toFixed(2);
        updateProductsSummary();
    }

    function updateProductNumbersAndButtons() {
        var items = document.querySelectorAll('.product-item');
        if (!items.length) return;
        items.forEach(function(item, i) {
            var sno = item.querySelector('.product-sno');
            if (sno) sno.textContent = i + 1;
            var btn = item.querySelector('.remove-product-btn');
            if (btn) btn.style.display = items.length > 1 ? 'inline-block' : 'none';
        });
    }

    function updateProductsSummary() {
        var total = 0;
        document.querySelectorAll('.product-price').forEach(function(el) {
            total += parseFloat(el.textContent) || 0;
        });
        var totalEl = document.getElementById('productsTotal');
        if (totalEl) totalEl.textContent = total.toFixed(2);

        var alertEl = document.getElementById('productsMatchAlert');
        var msgEl = document.getElementById('productsMatchMessage');
        if (!alertEl || !msgEl) return;

        var anyItem = Array.prototype.some.call(document.querySelectorAll('.product-name-input'), function(inp) {
            return inp.value.trim() !== '';
        });
        if (!anyItem) { alertEl.classList.add('d-none'); return; }

        var match = Math.abs(total - billAmount) <= 0.005;
        alertEl.classList.remove('d-none', 'alert-success', 'alert-warning');
        alertEl.classList.add(match ? 'alert-success' : 'alert-warning');
        msgEl.textContent = match
            ? 'Product total matches the Bill Amount.'
            : 'Product total (' + total.toFixed(2) + ' ৳) does not match the Bill Amount (' + billAmount.toFixed(2) + ' ৳).';
    }

    function addProductRow() {
        if (!productsContainer) return;
        var tr = document.createElement('tr');
        tr.className = 'product-item';
        tr.setAttribute('data-index', productIndex);
        tr.innerHTML = [
            '<td class="product-sno"></td>',
            '<td><input type="text" name="products[' + productIndex + '][product_name]" class="form-control form-control-sm product-name-input" placeholder="e.g. Round Neck T-Shirt" autocomplete="off" list="catalogProducts"></td>',
            '<td><input type="number" step="0.01" min="0" name="products[' + productIndex + '][rate]" class="form-control form-control-sm product-rate-input" placeholder="0.00"></td>',
            '<td><input type="number" step="0.01" min="0" name="products[' + productIndex + '][quantity]" class="form-control form-control-sm product-qty-input" placeholder="0"></td>',
            '<td class="text-end product-price fw-semibold">0.00</td>',
            '<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-product-btn"><i class="bi bi-trash"></i></button></td>'
        ].join('');
        productsContainer.appendChild(tr);
        productIndex++;
        updateProductNumbersAndButtons();
        updateProductsSummary();
    }

    if (addProductBtn) {
        addProductBtn.addEventListener('click', addProductRow);
    }

    if (productsContainer) {
        productsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-product-btn')) {
                e.target.closest('.product-item').remove();
                updateProductNumbersAndButtons();
                updateProductsSummary();
            }
        });
        productsContainer.addEventListener('input', function(e) {
            var row = e.target.closest('.product-item');
            if (e.target.classList.contains('product-name-input')) {
                var cat = lookupCatalogProduct(e.target.value);
                if (cat) {
                    var rateInput = row.querySelector('.product-rate-input');
                    if (rateInput && (rateInput.value === '' || parseFloat(rateInput.value) === 0)) {
                        rateInput.value = cat.rate || '';
                    }
                }
            }
            if (e.target.classList.contains('product-rate-input') || e.target.classList.contains('product-qty-input') || e.target.classList.contains('product-name-input')) {
                updateProductRow(row);
            }
        });
    }

    updateProductNumbersAndButtons();
    updateProductsSummary();
})();
</script>
@endsection