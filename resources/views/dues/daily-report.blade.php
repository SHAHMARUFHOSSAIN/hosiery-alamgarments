@extends('layouts.admin')

@section('title', __('Daily Due Report'))

@section('header', __('Daily Due Report') . ' - ' . now()->format('M d, Y'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dues.index') }}">{{ __('Dues') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Daily Report') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('Due Today') }}: {{ $todayDues->total() }}</h2>
    <div>
        <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event"></i> 
                {{ __('Total') }}: {{ format_number($totalAmount, 2) }}
            </h5>
            <a href="{{ route('export.dues', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> {{ __('Export') }}
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Mobile') }}</th>
                    <th>{{ __('Original') }}</th>
                    <th>{{ __('Paid') }}</th>
                    <th>{{ __('Remaining') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayDues as $due)
                <tr>
                    <td>{{ $due->id }}</td>
                    <td><a href="{{ route('customers.show', $due->customer) }}" class="fw-semibold">{{ $due->customer->name ?? 'N/A' }}</a></td>
                    <td>{{ $due->customer->location ?? 'N/A' }}</td>
                    <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                    <td>{{ format_number($due->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">{{ format_number($due->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold fs-5">{{ format_number($due->remaining_amount, 2) }}</td>
                    <td><span class="badge bg-danger text-white">{{ $due->due_date->format('M d, Y') }}</span></td>
                    <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#dailyPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> {{ __('Pay') }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-success">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                        <strong>{{ __('No pending dues for today!') }}</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($todayDues->total() > 0)
            <tfoot class="table-dark">
                <tr>
                    <td colspan="6" class="text-end"><strong>{{ __('Total Remaining:') }}</strong></td>
                    <td class="text-danger fw-bold fs-5">{{ format_number($totalAmount, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($todayDues->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $todayDues->links() !!}
    </div>
    @endif
</div>

@foreach($todayDues as $due)
<div class="modal fade" id="dailyPayModal{{ $due->id }}" tabindex="-1">
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