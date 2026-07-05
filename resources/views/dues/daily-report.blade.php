@extends('layouts.admin')

@section('title', 'Daily Due Report')

@section('header', 'Daily Due Report - ' . now()->format('M d, Y'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dues.index') }}">Dues</a></li>
        <li class="breadcrumb-item active">Daily Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">Due Today: {{ $todayDues->total() }}</h2>
    <div>
        <a href="{{ route('dues.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event"></i> 
                Total: {{ number_format($totalAmount, 2) }}
            </h5>
            <a href="{{ route('export.dues', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Location</th>
                    <th>Mobile</th>
                    <th>Original</th>
                    <th>Paid</th>
                    <th>Remaining</th>
                    <th>Due Date</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayDues as $due)
                <tr>
                    <td>{{ $due->id }}</td>
                    <td><a href="{{ route('customers.show', $due->customer) }}" class="fw-semibold">{{ $due->customer->name ?? 'N/A' }}</a></td>
                    <td>{{ $due->customer->location ?? 'N/A' }}</td>
                    <td>{{ $due->customer->mobile ?? 'N/A' }}</td>
                    <td>{{ number_format($due->original_amount, 2) }}</td>
                    <td class="text-success fw-bold">{{ number_format($due->total_paid, 2) }}</td>
                    <td class="text-danger fw-bold fs-5">{{ number_format($due->remaining_amount, 2) }}</td>
                    <td><span class="badge bg-danger text-white">{{ $due->due_date->format('M d, Y') }}</span></td>
                    <td><span class="badge bg-secondary">{{ $due->creator->name ?? 'N/A' }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#dailyPayModal{{ $due->id }}">
                            <i class="bi bi-credit-card"></i> Pay
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-success">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                        <strong>No pending dues for today!</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($todayDues->total() > 0)
            <tfoot class="table-dark">
                <tr>
                    <td colspan="6" class="text-end"><strong>Total Remaining:</strong></td>
                    <td class="text-danger fw-bold fs-5">{{ number_format($totalAmount, 2) }}</td>
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
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dues.add-payment') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="due_id" value="{{ $due->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Customer:</strong> {{ $due->customer->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Original Amount:</strong> ৳{{ number_format($due->original_amount, 2) }}
                    </div>
                    @if($due->hasPartialPayments())
                    <div class="mb-3">
                        <strong>Total Paid:</strong> <span class="text-success">৳{{ number_format($due->total_paid, 2) }}</span>
                    </div>
                    @endif
                    <div class="mb-3 alert alert-warning">
                        <strong>Remaining:</strong> <span class="text-danger fw-bold">৳{{ number_format($due->remaining_amount, 2) }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="payment_amount" id="payment_amount_{{ $due->id }}"
                                   class="form-control"
                                   max="{{ $due->remaining_amount }}" value="{{ $due->remaining_amount }}"
                                    oninput="updatePaymentAmount(this, {{ $due->remaining_amount }}, {{ $due->total_discount }})" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount <small class="text-muted">(optional, auto subtracts)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="discount" id="discount_{{ $due->id }}"
                                   class="form-control" value="0" min="0" max="{{ $due->remaining_amount }}"
                                    oninput="updateDiscount(this, {{ $due->remaining_amount }}, {{ $due->total_discount }})">
                        </div>
                        <div id="discount_info_{{ $due->id }}" class="form-text mt-1">
                            Total discount: ৳{{ number_format($due->total_discount, 2) }} | Remaining after: ৳{{ number_format($due->remaining_amount, 2) }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required onchange="toggleChequeFields(this)">
                            <option value="cash">Cash</option>
                            <option value="check">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="card border border-warning mb-3 cheque-fields" style="display:none;">
                        <div class="card-header bg-warning bg-opacity-10 py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> Cheque Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6" style="position: relative;">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="bank_name" class="form-control bank-search-input" placeholder="Search bank name..." autocomplete="off" data-req="1">
                                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="bank-results list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cheque No <span class="text-danger">*</span></label>
                                    <input type="text" name="check_no" class="form-control" data-req="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
                                    <input type="date" name="check_date" class="form-control" data-req="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="check_amount" class="form-control" data-req="1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reminder Date</label>
                                    <input type="date" name="check_reminder_date" class="form-control">
                                    <small class="text-muted">Date to remind before check date</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Cheque Photo</label>
                                    <input type="file" name="check_photo" class="form-control" accept="image/*">
                                    <small class="text-muted">Upload cheque image (max 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Due Date <small class="text-muted">(if remaining balance)</small></label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID <small class="text-muted">(for reference)</small></label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Record Payment
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
                <h5 class="modal-title">New Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newBankForm" method="POST" action="{{ route('banks.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bank_modal_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="bank_modal_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create & Select</button>
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