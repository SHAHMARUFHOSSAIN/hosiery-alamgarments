@extends('layouts.admin')

@section('title', __('Dues'))
@section('header', __('Dues'))

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">{{ __('Search') }}</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('Customer or bill no...') }}" 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
</div>

<h2 class="mb-4">{{ __('All Dues') }} ({{ $dues->total() }})</h2>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Dues') }}</h6>
                <h3 class="text-danger mb-0">{{ format_currency($totalPendingAmount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">{{ __('Total Records') }}</h6>
                <h3 class="mb-0">{{ $dues->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Bill') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'original_amount', 'direction' => request('sort') == 'original_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            {{ __('Original') }} @if(request('sort') == 'original_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Paid') }}</th>
                    <th>{{ __('Discount') }}</th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'remaining_amount', 'direction' => request('sort') == 'remaining_amount' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            {{ __('Remaining') }} @if(request('sort') == 'remaining_amount'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dues.index', ['sort' => 'due_date', 'direction' => request('sort') == 'due_date' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->only('status', 'search')) }}" class="text-decoration-none">
                            {{ __('Due Date') }} @if(request('sort') == 'due_date'){{ request('direction') == 'asc' ? '▲' : '▼' }}@endif
                        </a>
                    </th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dues as $due)
                @php
                    $isPaid = $due->remaining_amount <= 0;
                    $isPartial = $due->hasPartialPayments() && !$isPaid;
                @endphp
                <tr class="{{ $isPaid ? '' : ($due->due_date->isPast() ? 'table-danger' : '') }}">
                    <td>
                        <strong>{{ $due->customer->name ?? __('Unknown') }}</strong>
                        @if($due->customer->mobile)
                        <br><small>{{ $due->customer->mobile }}</small>
                        @endif
                    </td>
                    <td>{{ $due->customer->location ?? 'N/A' }}</td>
                    <td><a href="{{ route('bills.show', $due->bill) }}">{{ $due->bill->bill_no ?? 'N/A' }}</a></td>
                    <td>{{ $due->bill->report_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ format_currency($due->original_amount) }}</td>
                    <td class="text-success fw-bold">{{ format_currency($due->total_paid) }}</td>
                    <td class="text-warning fw-bold">{{ format_currency($due->total_discount) }}</td>
                    <td class="text-danger fw-bold">{{ format_currency($due->remaining_amount) }}</td>
                    <td>{{ $due->due_date->format('M d, Y') }}</td>
                    <td>
                        @if($isPaid)
                        <span class="badge bg-success">{{ __('Paid') }}</span>
                        @elseif($isPartial)
                        <span class="badge bg-info text-dark">{{ __('Partial') }}</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td>
                        @if(!$isPaid)
                        <button type="button" class="btn btn-success btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#paymentModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> {{ __('Pay') }}
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center py-4"><strong>{{ __('No dues found') }}</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dues->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $dues->appends(request()->only('status', 'search', 'sort', 'direction'))->links() !!}
    </div>
    @endif
</div>

@foreach($dues as $due)
@if($due->remaining_amount > 0)
<div class="modal fade" id="paymentModal{{ $due->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Make Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>{{ __('Customer:') }}</strong> {{ $due->customer->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Original Amount:') }}</strong> {{ format_currency($due->original_amount) }}
                    </div>
                    @if($due->hasPartialPayments())
                    <div class="mb-3">
                        <strong>{{ __('Total Paid:') }}</strong> <span class="text-success">{{ format_currency($due->total_paid) }}</span>
                    </div>
                    @endif
                    @if($due->total_discount > 0)
                    <div class="mb-3">
                        <strong>{{ __('Total Discount:') }}</strong> <span class="text-warning">{{ format_currency($due->total_discount) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>{{ __('Remaining:') }}</strong> <span class="text-danger fw-bold">{{ format_currency($due->remaining_amount) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" id="payment_amount_{{ $due->id }}"
                                   class="form-control"
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}"
                                   oninput="updatePaymentAmount(this, {{ $due->remaining_amount }}, {{ $due->total_discount }})" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Discount') }} <small class="text-muted">{{ __('(optional, auto subtracts)') }}</small></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="discount" id="discount_{{ $due->id }}"
                                   class="form-control" value="0" min="0" max="{{ $due->remaining_amount }}"
                                   oninput="updateDiscount(this, {{ $due->remaining_amount }}, {{ $due->total_discount }})">
                        </div>
                        <div id="discount_info_{{ $due->id }}" class="form-text mt-1">
                            {{ __('Total discount:') }} {{ format_currency($due->total_discount) }} | {{ __('Remaining after:') }} {{ format_currency($due->remaining_amount) }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required onchange="toggleChequeFields(this)">
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="check">{{ __('Cheque') }}</option>
                            <option value="mobile_banking">{{ __('Mobile Banking') }}</option>
                        </select>
                    </div>
                    <div class="card border border-warning mb-3 cheque-fields" style="display:none;">
                        <div class="card-header bg-warning bg-opacity-10 py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> {{ __('Cheque Payment Details') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6" style="position: relative;">
                                    <label class="form-label">{{ __('Bank Name') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="bank_name" class="form-control bank-search-input" placeholder="{{ __('Search bank name...') }}" autocomplete="off" data-req="1">
                                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="bank-results list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Cheque No') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="check_no" class="form-control" data-req="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Cheque Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="check_date" class="form-control" data-req="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Cheque Amount') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="check_amount" class="form-control" data-req="1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Reminder Date') }}</label>
                                    <input type="date" name="check_reminder_date" class="form-control">
                                    <small class="text-muted">{{ __('Date to remind before cheque date') }}</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Cheque Photo') }}</label>
                                    <input type="file" name="check_photo" class="form-control" accept="image/*">
                                    <small class="text-muted">{{ __('Upload cheque image (max 5MB)') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Next Due Date') }} <small class="text-muted">{{ __('(if remaining balance)') }}</small></label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Transaction ID') }} <small class="text-muted">{{ __('(for reference)') }}</small></label>
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

<div class="modal fade" id="newBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('New Bank') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newBankForm" method="POST" action="{{ route('banks.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bank_modal_name" class="form-label">{{ __('Bank Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="bank_modal_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create & Select') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
var banksSearchUrl = '{{ route("banks.search") }}';
var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function setupBankSearch(input) {
    if (!input) return;
    var results = input.closest('.col-md-6')?.querySelector('.bank-results');
    if (!results) return;
    var searchTimeout;
    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var term = this.value.trim();
        if (term.length < 1) { results.style.display = 'none'; return; }
        results.innerHTML = '<div class="list-group-item text-muted text-center py-2">Searching...</div>';
        results.style.display = 'block';
        searchTimeout = setTimeout(function() {
            fetch(banksSearchUrl + '?term=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(data) {
                results.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    results.innerHTML = '<div class="list-group-item text-muted text-center py-2">No results</div>';
                    return;
                }
                data.forEach(function(item) {
                    var el = document.createElement('a');
                    el.href = '#';
                    el.className = 'list-group-item list-group-item-action';
                    el.setAttribute('data-json', JSON.stringify(item));
                    el.textContent = item.name;
                    results.appendChild(el);
                });
            })
            .catch(function(err) {
                console.error('Search error:', err);
                results.innerHTML = '<div class="list-group-item text-danger text-center py-2">Error</div>';
            });
        }, 250);
    });
    results.addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        var item = e.target.closest('.list-group-item');
        if (!item || !item.hasAttribute('data-json')) return;
        var data = JSON.parse(item.getAttribute('data-json'));
        if (data && data.name) input.value = data.name;
        results.style.display = 'none';
    });
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) results.style.display = 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.bank-search-input')) setupBankSearch(document.querySelector('.bank-search-input'));

    var bankForm = document.getElementById('newBankForm');
    if (bankForm) {
        bankForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var fd = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var bankName = fd.get('name');
                    document.querySelectorAll('.bank-search-input').forEach(function(inp) {
                        if (inp.value === '' || inp.dataset.fromModal) {
                            inp.value = bankName;
                            inp.dataset.fromModal = 'true';
                        }
                    });
                    var modalEl = document.getElementById('newBankModal');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    bankForm.reset();
                }
            })
            .catch(function(err) { console.error('Error creating bank:', err); });
        });
    }
});

function toggleChequeFields(el) {
    var modal = el.closest('.modal');
    if (!modal) return;
    var chequeFields = modal.querySelector('.cheque-fields');
    if (!chequeFields) return;
    var isCheck = el.value === 'check';
    chequeFields.style.display = isCheck ? '' : 'none';
    chequeFields.querySelectorAll('input:not([type=file]):not([type=hidden]), select, textarea').forEach(function(f) {
        if (isCheck) {
            if (f.hasAttribute('data-req')) f.required = true;
        } else {
            f.required = false;
            f.value = '';
        }
    });
    if (isCheck) {
        var fileInput = chequeFields.querySelector('input[type=file]');
        if (fileInput) fileInput.value = '';
    }
}
function getDueId(el) {
    var parts = el.id.split('_');
    return parts[parts.length - 1];
}
function updateDiscount(el, remaining, existingDiscount) {
    var dueId = getDueId(el);
    var discount = parseFloat(el.value) || 0;
    var form = el.closest('form');
    var infoEl = document.getElementById('discount_info_' + dueId);
    var paymentInput = form ? form.querySelector('[name="payment_amount"]') : null;
    var effectiveRemaining = Math.max(0, remaining - discount);
    var totalDiscount = existingDiscount + discount;
    if (infoEl) infoEl.textContent = 'Total discount: ৳' + totalDiscount.toFixed(2) + ' | Remaining after: ৳' + effectiveRemaining.toFixed(2);
    if (paymentInput) {
        paymentInput.max = effectiveRemaining;
        if (parseFloat(paymentInput.value) > effectiveRemaining) {
            paymentInput.value = effectiveRemaining;
        }
    }
}
function updatePaymentAmount(el, remaining, existingDiscount) {
    var dueId = getDueId(el);
    var discountInput = document.getElementById('discount_' + dueId);
    var infoEl = document.getElementById('discount_info_' + dueId);
    var discount = discountInput ? (parseFloat(discountInput.value) || 0) : 0;
    var payVal = parseFloat(el.value) || 0;
    var effectiveRemaining = Math.max(0, remaining - discount);
    var totalDiscount = existingDiscount + discount;
    if (payVal > effectiveRemaining) {
        el.value = effectiveRemaining;
    }
    if (infoEl) infoEl.textContent = 'Total discount: ৳' + totalDiscount.toFixed(2) + ' | Remaining after: ৳' + effectiveRemaining.toFixed(2);
}
</script>
@endpush
@endsection
