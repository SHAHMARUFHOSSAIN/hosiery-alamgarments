@extends('layouts.admin')

@section('title', 'Edit Bill')

@section('header', 'Edit Bill')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">Bills</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">Edit Bill</h2>
    <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('bills.update', $bill) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="bill_no" class="form-label">Bill No <span class="text-danger">*</span></label>
                    <input type="text" name="bill_no" id="bill_no" 
                           class="form-control @error('bill_no') is-invalid @enderror" 
                           value="{{ old('bill_no', $bill->bill_no) }}" required>
                    @error('bill_no')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="report_date" class="form-label">Report Date <span class="text-danger">*</span></label>
                    <input type="date" name="report_date" id="report_date" 
                           class="form-control @error('report_date') is-invalid @enderror" 
                           value="{{ old('report_date', $bill->report_date?->format('Y-m-d')) }}" required>
                    @error('report_date')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="shop_name" class="form-label">Shop Name</label>
                    <input type="text" name="shop_name" id="shop_name" 
                           class="form-control @error('shop_name') is-invalid @enderror" 
                           value="{{ old('shop_name', $bill->shop_name) }}">
                    @error('shop_name')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="bill_man" class="form-label">Bill Man</label>
                    <input type="text" name="bill_man" id="bill_man" 
                           class="form-control @error('bill_man') is-invalid @enderror" 
                           value="{{ old('bill_man', $bill->bill_man) }}">
                    @error('bill_man')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="bill_amount" class="form-label">Bill Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" name="bill_amount" id="bill_amount" 
                               class="form-control @error('bill_amount') is-invalid @enderror" 
                               value="{{ old('bill_amount', $bill->bill_amount) }}" required>
                    </div>
                    @error('bill_amount')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="discount" class="form-label">Discount</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" name="discount" id="discount" 
                               class="form-control @error('discount') is-invalid @enderror" 
                               value="{{ old('discount', $bill->discount) }}">
                    </div>
                </div>

                @php
                    $mainPayment = $bill->payments()->where('payment_type', '!=', 'cash')->first();
                    $cashPayment = $bill->payments()->where('payment_type', 'cash')->first();
                    $checkPayments = $bill->payments()->where('payment_type', 'check')->get();
                    $currentType = old('payment_type', $mainPayment?->payment_type ?? 'cash');
                @endphp
                <div class="col-md-6">
                    <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                        <option value="cash" {{ $currentType == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="check" {{ $currentType == 'check' ? 'selected' : '' }}>Cheque</option>
                        <option value="tt" {{ $currentType == 'tt' ? 'selected' : '' }}>TT</option>
                        <option value="card" {{ $currentType == 'card' ? 'selected' : '' }}>Reference Card</option>
                        <option value="due" {{ $currentType == 'due' ? 'selected' : '' }}>Due</option>
                    </select>
                    @error('payment_type')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="payment_amount" class="form-label">Payment Received</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" name="payment_amount" id="payment_amount" 
                               class="form-control" value="{{ old('payment_amount', $cashPayment?->amount ?? 0) }}">
                    </div>
                    <small class="text-muted">Amount received now (if any)</small>
                </div>
                <div class="col-md-6">
                    <label for="payment_details" class="form-label">Payment Details</label>
                    <input type="text" name="payment_details" id="payment_details" 
                           class="form-control" value="{{ old('payment_details', $mainPayment?->details) }}">
                </div>

                {{-- Due Date --}}
                <div class="col-md-6" id="due_date_field" style="{{ $currentType == 'due' ? '' : 'display: none;' }}">
                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" id="due_date" 
                           class="form-control @error('due_date') is-invalid @enderror" 
                           value="{{ old('due_date', optional($bill->dues()->first())->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d')) }}">
                    @error('due_date')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Check Fields --}}
                <div class="col-12" id="checkFields" style="{{ $currentType == 'check' ? '' : 'display: none;' }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-warning"><i class="bi bi-bank"></i> Cheque Payment Details</h6>
                        <button type="button" class="btn btn-sm btn-warning" id="addCheckBtn">
                            <i class="bi bi-plus"></i> Add Another Cheque
                        </button>
                    </div>
                    <div id="checkPaymentsContainer">
                        @error('checks')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        @php $checkIndex = 0; @endphp
                        @forelse($checkPayments as $cp)
                        <div class="card border border-warning mb-3 check-payment-item" data-index="{{ $checkIndex }}">
                            <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-bank"></i> Cheque Payment #{{ $loop->iteration }}</span>
                                <button type="button" class="btn btn-sm btn-danger remove-check-btn" style="{{ $checkPayments->count() > 1 ? '' : 'display: none;' }}">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6" style="position: relative;">
                                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="checks[{{ $checkIndex }}][bank_name]" class="form-control bank-search-input" placeholder="Search bank name..." autocomplete="off" value="{{ old('checks.' . $checkIndex . '.bank_name', $cp->bank_name) }}" required>
                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <div class="bank-results list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cheque No <span class="text-danger">*</span></label>
                                        <input type="text" name="checks[{{ $checkIndex }}][check_no]" class="form-control" value="{{ old('checks.' . $checkIndex . '.check_no', $cp->check_no) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cheque Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" name="checks[{{ $checkIndex }}][check_amount]" class="form-control check-amount-input" value="{{ old('checks.' . $checkIndex . '.check_amount', $cp->check_amount) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
                                        <input type="date" name="checks[{{ $checkIndex }}][check_date]" class="form-control" value="{{ old('checks.' . $checkIndex . '.check_date', $cp->check_date?->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Reminder Date</label>
                                        <input type="date" name="checks[{{ $checkIndex }}][check_reminder_date]" class="form-control" value="{{ old('checks.' . $checkIndex . '.check_reminder_date', $cp->check_reminder_date?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Status</label>
                                        <div>
                                            @if($cp->status === 'encashed')
                                            <span class="badge bg-success">Encashed</span>
                                            @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cheque Photo</label>
                                        @if($cp->check_photo)
                                        <div class="mb-2">
                                            <a href="{{ route('cheque.show', $cp->check_photo) }}" target="_blank">
                                                <img src="{{ route('cheque.show', $cp->check_photo) }}" alt="Cheque" class="img-thumbnail" style="width: 150px; height: 75px; object-fit: cover;">
                                            </a>
                                        </div>
                                        @else
                                        <span class="text-muted">No photo uploaded</span>
                                        @endif
                                        <input type="file" name="checks[{{ $checkIndex }}][check_photo]" class="form-control form-control-sm" accept="image/*">
                                        <small class="text-muted">Upload cheque image (max 5MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php $checkIndex++; @endphp
                        @empty
                        <div class="card border border-warning mb-3 check-payment-item" data-index="0">
                            <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-bank"></i> Cheque Payment #1</span>
                                <button type="button" class="btn btn-sm btn-danger remove-check-btn" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6" style="position: relative;">
                                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="checks[0][bank_name]" class="form-control bank-search-input" placeholder="Search bank name..." autocomplete="off" required>
                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <div class="bank-results list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cheque No <span class="text-danger">*</span></label>
                                        <input type="text" name="checks[0][check_no]" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cheque Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" name="checks[0][check_amount]" class="form-control check-amount-input" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
                                        <input type="date" name="checks[0][check_date]" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Reminder Date</label>
                                        <input type="date" name="checks[0][check_reminder_date]" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cheque Photo</label>
                                        <input type="file" name="checks[0][check_photo]" class="form-control form-control-sm" accept="image/*">
                                        <small class="text-muted">Upload cheque image (max 5MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="alert alert-info">
                        <strong>Total Cheque Amount: </strong><span id="totalCheckAmount">0.00</span>
                        <br>
                        <strong>Remaining Due: </strong><span id="checkRemainingDue" class="fw-bold text-danger">0.00</span>
                    </div>
                </div>

                {{-- TT Fields --}}
                <div class="col-12" id="ttFields" style="{{ $currentType == 'tt' ? '' : 'display: none;' }}">
                    <div class="card border border-info">
                        <div class="card-header bg-info text-dark py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> TT Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tt_bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="tt_bank_name" id="tt_bank_name" class="form-control @error('tt_bank_name') is-invalid @enderror" value="{{ old('tt_bank_name', $mainPayment?->tt_bank_name) }}">
                                    @error('tt_bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tt_account_no" class="form-label">Account No</label>
                                    <input type="text" name="tt_account_no" id="tt_account_no" class="form-control @error('tt_account_no') is-invalid @enderror" value="{{ old('tt_account_no', $mainPayment?->tt_account_no) }}">
                                    @error('tt_account_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tt_amount" class="form-label">TT Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="tt_amount" id="tt_amount" class="form-control @error('tt_amount') is-invalid @enderror" value="{{ old('tt_amount', $mainPayment?->tt_amount) }}">
                                    </div>
                                    @error('tt_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tt_date" class="form-label">TT Date <span class="text-danger">*</span></label>
                                    <input type="date" name="tt_date" id="tt_date" class="form-control @error('tt_date') is-invalid @enderror" value="{{ old('tt_date', $mainPayment?->tt_date?->format('Y-m-d')) }}">
                                    @error('tt_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Fields --}}
                <div class="col-12" id="cardFields" style="{{ $currentType == 'card' ? '' : 'display: none;' }}">
                    <div class="card border border-secondary">
                        <div class="card-header bg-secondary text-white py-2">
                            <h6 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Reference Card Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="card_reference" class="form-label">Reference Card <span class="text-danger">*</span></label>
                                    <input type="text" name="card_reference" id="card_reference" class="form-control @error('card_reference') is-invalid @enderror" value="{{ old('card_reference', $mainPayment?->card_reference) }}">
                                    @error('card_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <input type="text" name="card_location" id="card_location" class="form-control @error('card_location') is-invalid @enderror" value="{{ old('card_location', $mainPayment?->card_location) }}">
                                    @error('card_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_amount" class="form-label">Card Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="card_amount" id="card_amount" class="form-control @error('card_amount') is-invalid @enderror" value="{{ old('card_amount', $mainPayment?->card_amount) }}">
                                    </div>
                                    @error('card_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_date" class="form-label">Card Date <span class="text-danger">*</span></label>
                                    <input type="date" name="card_date" id="card_date" class="form-control @error('card_date') is-invalid @enderror" value="{{ old('card_date', $mainPayment?->card_date?->format('Y-m-d')) }}">
                                    @error('card_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Bill
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- New Bank Modal --}}
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
(function() {
    'use strict';

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var banksSearchUrl = '{{ route("banks.search") }}';

    // ---- Payment type toggle ----
    document.getElementById('payment_type')?.addEventListener('change', function() {
        var val = this.value;
        document.getElementById('due_date_field').style.display = val === 'due' ? '' : 'none';
        document.getElementById('checkFields').style.display = val === 'check' ? '' : 'none';
        document.getElementById('ttFields').style.display = val === 'tt' ? '' : 'none';
        document.getElementById('cardFields').style.display = val === 'card' ? '' : 'none';
        var cf = document.getElementById('checkFields');
        if (cf) {
            cf.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.disabled = val !== 'check';
            });
        }
    });

    // Set initial disabled state on page load
    document.getElementById('payment_type')?.dispatchEvent(new Event('change'));

    // ---- Dynamic check payments ----
    var checkIndex = {{ $checkPayments->count() > 0 ? $checkPayments->count() : 1 }};
    var addCheckBtn = document.getElementById('addCheckBtn');
    var checkContainer = document.getElementById('checkPaymentsContainer');

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

    if (addCheckBtn) {
        addCheckBtn.addEventListener('click', function() {
            if (!checkContainer) return;
            var div = document.createElement('div');
            div.className = 'card border border-warning mb-3 check-payment-item';
            div.setAttribute('data-index', checkIndex);
            div.innerHTML = [
                '<div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">',
                '  <span><i class="bi bi-bank"></i> Cheque Payment #' + (checkIndex + 1) + '</span>',
                '  <button type="button" class="btn btn-sm btn-danger remove-check-btn"><i class="bi bi-trash"></i> Remove</button>',
                '</div>',
                '<div class="card-body"><div class="row g-3">',
                '  <div class="col-md-6" style="position: relative;">',
                '    <label class="form-label">Bank Name <span class="text-danger">*</span></label>',
                '    <div class="input-group">',
                '      <input type="text" name="checks[' + checkIndex + '][bank_name]" class="form-control bank-search-input" placeholder="Search bank name..." autocomplete="off" required>',
                '      <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal"><i class="bi bi-plus"></i></button>',
                '    </div>',
                '    <div class="bank-results list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>',
                '  </div>',
                '  <div class="col-md-6"><label class="form-label">Cheque No <span class="text-danger">*</span></label><input type="text" name="checks[' + checkIndex + '][check_no]" class="form-control" required></div>',
                '  <div class="col-md-4"><label class="form-label">Cheque Amount <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">৳</span><input type="number" step="0.01" name="checks[' + checkIndex + '][check_amount]" class="form-control check-amount-input" required></div></div>',
                '  <div class="col-md-4"><label class="form-label">Cheque Date <span class="text-danger">*</span></label><input type="date" name="checks[' + checkIndex + '][check_date]" class="form-control" required></div>',
                '  <div class="col-md-4"><label class="form-label">Reminder Date</label><input type="date" name="checks[' + checkIndex + '][check_reminder_date]" class="form-control"></div>',
                '  <div class="col-md-4"><label class="form-label">Cheque Photo</label><input type="file" name="checks[' + checkIndex + '][check_photo]" class="form-control" accept="image/*"><small class="text-muted">Upload cheque image (max 5MB)</small></div>',
                '</div></div></div>'
            ].join('');
            checkContainer.appendChild(div);
            var curPtype = document.getElementById('payment_type')?.value;
            if (curPtype !== 'check') {
                div.querySelectorAll('input, select, textarea').forEach(function(el) {
                    el.disabled = true;
                });
            }
            setupBankSearch(div.querySelector('.bank-search-input'));
            checkIndex++;
            updateCheckButtons();
            updateCheckTotal();
        });
    }

    if (checkContainer) {
        checkContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-check-btn')) {
                e.target.closest('.check-payment-item').remove();
                updateCheckButtons();
                updateCheckTotal();
            }
        });
    }

    function updateCheckButtons() {
        var items = document.querySelectorAll('.check-payment-item');
        items.forEach(function(item, i) {
            var btn = item.querySelector('.remove-check-btn');
            if (btn) btn.style.display = items.length > 1 ? 'block' : 'none';
            var header = item.querySelector('.card-header span');
            if (header) header.innerHTML = '<i class="bi bi-bank"></i> Cheque Payment #' + (i + 1);
        });
    }

    function updateCheckTotal() {
        var total = 0;
        document.querySelectorAll('.check-amount-input').forEach(function(inp) {
            total += parseFloat(inp.value) || 0;
        });
        var el = document.getElementById('totalCheckAmount');
        if (el) el.textContent = total.toFixed(2);
        updateCheckRemainingDue();
    }

    function updateCheckRemainingDue() {
        var billAmt = document.getElementById('bill_amount');
        var discAmt = document.getElementById('discount');
        var payAmt = document.getElementById('payment_amount');
        var bill = parseFloat(billAmt ? billAmt.value : 0) || 0;
        var disc = parseFloat(discAmt ? discAmt.value : 0) || 0;
        var totalCheck = parseFloat((document.getElementById('totalCheckAmount') || {}).textContent) || 0;
        var other = parseFloat(payAmt ? payAmt.value : 0) || 0;
        var remain = bill - disc - totalCheck - other;
        var el = document.getElementById('checkRemainingDue');
        if (el) el.textContent = remain.toFixed(2);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('check-amount-input')) updateCheckTotal();
        if (e.target.id === 'bill_amount' || e.target.id === 'discount' || e.target.id === 'payment_amount') {
            updateCheckRemainingDue();
        }
    });

    // Initialize bank search for existing items
    document.querySelectorAll('.bank-search-input').forEach(function(inp) {
        setupBankSearch(inp);
    });

    updateCheckButtons();
    updateCheckTotal();

    // ---- New Bank Form (AJAX) ----
    (function() {
        var form = document.getElementById('newBankForm');
        if (!form) return;
        var modalEl = document.getElementById('newBankModal');
        if (!modalEl) return;
        var modal;
        try { modal = new bootstrap.Modal(modalEl); } catch(e) { return; }
        form.addEventListener('submit', function(e) {
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
                    modal.hide();
                    form.reset();
                }
            })
            .catch(function(err) { console.error('Error creating bank:', err); });
        });
    })();
})();
</script>
@endpush
@endsection