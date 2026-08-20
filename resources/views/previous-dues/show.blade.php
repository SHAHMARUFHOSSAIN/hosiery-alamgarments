@extends('layouts.admin')

@section('title', __('Previous Due') . ' #' . $previousDue->id)
@section('header', __('Previous Due') . ' #' . $previousDue->id)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('previous-dues.index') }}">{{ __('Previous Dues') }}</a></li>
        <li class="breadcrumb-item active">#{{ $previousDue->id }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Details') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted">{{ __('Customer') }}</th>
                        <td class="fw-semibold">{{ $previousDue->customer->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Mobile') }}</th>
                        <td>{{ $previousDue->customer->mobile ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Original Amount') }}</th>
                        <td class="fw-bold">{{ format_currency($previousDue->original_amount) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Total Paid') }}</th>
                        <td class="fw-bold text-success">{{ format_currency($previousDue->total_paid) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Remaining') }}</th>
                        <td class="fw-bold text-danger fs-5">{{ format_currency($previousDue->remaining_amount) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Status') }}</th>
                        <td>
                            @if($previousDue->status == 'paid')
                            <span class="badge bg-success">{{ __('Paid') }}</span>
                            @else
                            <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Notes') }}</th>
                        <td>{{ $previousDue->notes ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Created By') }}</th>
                        <td>{{ $previousDue->creator->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Created At') }}</th>
                        <td>{{ $previousDue->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Updated At') }}</th>
                        <td>{{ $previousDue->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Customer Info') }}</h5>
            </div>
            <div class="card-body">
                @if($previousDue->customer)
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted">{{ __('Name') }}</th>
                        <td>
                            <a href="{{ route('customers.show', $previousDue->customer) }}" class="fw-semibold">
                                {{ $previousDue->customer->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Mobile') }}</th>
                        <td>{{ $previousDue->customer->mobile ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">{{ __('Opening Balance') }}</th>
                        <td class="fw-bold {{ $previousDue->customer->opening_balance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ format_currency($previousDue->customer->opening_balance) }}
                        </td>
                    </tr>
                </table>
                @else
                <p class="text-muted mb-0">{{ __('Customer not found.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($previousDue->status == 'pending')
<div class="mb-4">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
        <i class="bi bi-credit-card"></i> {{ __('Make Payment') }}
    </button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">{{ __('Payment History') }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Payment Type') }}</th>
                    <th>{{ __('Bank / Cheque') }}</th>
                    <th>{{ __('Remaining') }}</th>
                    <th>{{ __('Note') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Recorded By') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previousDue->payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td class="fw-bold text-success">{{ format_currency($payment->amount) }}</td>
                    <td>
                        @php
                            $badge = match($payment->payment_type) {
                                'cash' => 'bg-success',
                                'check' => 'bg-warning text-dark',
                                'mobile_banking' => 'bg-info text-dark',
                                default => 'bg-secondary'
                            };
                            $label = match($payment->payment_type) {
                                'check' => 'CHEQUE',
                                'mobile_banking' => 'MOBILE BANKING',
                                default => strtoupper($payment->payment_type)
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td>
                        @if($payment->bank_name)
                            {{ $payment->bank_name }} @if($payment->check_no) ({{ $payment->check_no }}) @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ format_currency($payment->remaining_amount) }}</td>
                    <td>{{ $payment->note ?? '—' }}</td>
                    <td><small>{{ $payment->payment_date->format('M d, Y') }}</small></td>
                    <td><small>{{ $payment->user->name ?? 'N/A' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4"><strong>{{ __('No payments yet') }}</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="{{ route('previous-dues.edit', $previousDue) }}" class="btn btn-primary">
        <i class="bi bi-pencil"></i> {{ __('Edit') }}
    </a>
    <a href="{{ route('previous-dues.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

@if($previousDue->status == 'pending')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Make Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('previous-dues.add-payment') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="previous_due_id" value="{{ $previousDue->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>{{ __('Customer:') }}</strong> {{ $previousDue->customer->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Original Amount:') }}</strong> {{ format_currency($previousDue->original_amount) }}
                    </div>
                    @if($previousDue->hasPartialPayments())
                    <div class="mb-3">
                        <strong>{{ __('Total Paid:') }}</strong> <span class="text-success">{{ format_currency($previousDue->total_paid) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>{{ __('Remaining:') }}</strong> <span class="text-danger fw-bold">{{ format_currency($previousDue->remaining_amount) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" id="payment_amount_{{ $previousDue->id }}"
                                   class="form-control"
                                   max="{{ $previousDue->remaining_amount }}" value="{{ $previousDue->remaining_amount }}"
                                    oninput="updatePaymentAmount(this, {{ $previousDue->remaining_amount }}, {{ $previousDue->total_discount }})" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Discount') }} <small class="text-muted">({{ __('optional, auto subtracts') }})</small></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="discount" id="discount_{{ $previousDue->id }}"
                                   class="form-control" value="0" min="0" max="{{ $previousDue->remaining_amount }}"
                                    oninput="updateDiscount(this, {{ $previousDue->remaining_amount }}, {{ $previousDue->total_discount }})">
                        </div>
                        <div id="discount_info_{{ $previousDue->id }}" class="form-text mt-1">
                            {{ __('Total discount:') }} {{ format_currency($previousDue->total_discount) }} | {{ __('Remaining after:') }} {{ format_currency($previousDue->remaining_amount) }}
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
                                    <small class="text-muted">{{ __('Date to remind before check date') }}</small>
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
