@extends('layouts.admin')

@section('title', 'Create Bill')

@section('header', 'Create Bill')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">Bills</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
</nav>
@endsection

@push('styles')
<style>
    .search-dropdown .list-group-item {
        cursor: pointer;
        border: 1px solid #dee2e6;
        background: #fff;
    }
    .search-dropdown .list-group-item:hover {
        background-color: #f8f9fa;
        border-color: #86b7fe;
    }
    .search-dropdown .list-group-item.active,
    .search-dropdown .list-group-item:active {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #212529;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Bill</h5>
                    <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bills.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12" style="position: relative;">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="customerSearch" class="form-control" placeholder="Search by name or phone..." autocomplete="off" required>
                                <input type="hidden" name="customer_id" id="customerId">
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                    <i class="bi bi-plus"></i> New
                                </button>
                            </div>
                            <div id="customerResults" class="list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="bill_no" class="form-label">Bill No <span class="text-danger">*</span></label>
                            <input type="text" name="bill_no" id="bill_no" class="form-control @error('bill_no') is-invalid @enderror" value="{{ old('bill_no') }}" required>
                            @error('bill_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="shop_name" class="form-label">Shop Name</label>
                            <input type="text" name="shop_name" id="shop_name" class="form-control" value="{{ old('shop_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="bill_man" class="form-label">Bill Man</label>
                            <input type="text" name="bill_man" id="bill_man" class="form-control" value="{{ old('bill_man') }}">
                        </div>

                        <div class="col-md-4">
                            <label for="bill_amount" class="form-label">Bill Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="bill_amount" id="bill_amount" class="form-control @error('bill_amount') is-invalid @enderror" value="{{ old('bill_amount') }}" required>
                            </div>
                            @error('bill_amount')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="discount" class="form-label">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="{{ old('discount', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="check" {{ old('payment_type') == 'check' ? 'selected' : '' }}>Check</option>
                                <option value="tt" {{ old('payment_type') == 'tt' ? 'selected' : '' }}>TT</option>
                                <option value="card" {{ old('payment_type') == 'card' ? 'selected' : '' }}>Card</option>
                                <option value="due" {{ old('payment_type') == 'due' ? 'selected' : '' }}>Due</option>
                            </select>
                            @error('payment_type')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="payment_amount" class="form-label">Payment Received</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="payment_amount" id="payment_amount" class="form-control" value="{{ old('payment_amount', 0) }}">
                            </div>
                            <small class="text-muted">Amount received now (if any)</small>
                        </div>
                        <div class="col-md-4">
                            <label for="payment_details" class="form-label">Payment Details</label>
                            <input type="text" name="payment_details" id="payment_details" class="form-control" value="{{ old('payment_details') }}">
                        </div>
                        <div class="col-md-4" id="dueDateSection" style="display: none;">
                            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                            @error('due_date')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12" id="checkFields" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-warning"><i class="bi bi-bank"></i> Check Payment Details</h6>
                                <button type="button" class="btn btn-sm btn-warning" id="addCheckBtn">
                                    <i class="bi bi-plus"></i> Add Another Check
                                </button>
                            </div>
                            <div id="checkPaymentsContainer">
                                <div class="card border border-warning mb-3 check-payment-item" data-index="0">
                                    <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-bank"></i> Check Payment #1</span>
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
                                                <label class="form-label">Check No <span class="text-danger">*</span></label>
                                                <input type="text" name="checks[0][check_no]" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Check Date <span class="text-danger">*</span></label>
                                                <input type="date" name="checks[0][check_date]" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Check Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>
                                                    <input type="number" step="0.01" name="checks[0][check_amount]" class="form-control check-amount-input" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Reminder Date</label>
                                                <input type="date" name="checks[0][check_reminder_date]" class="form-control">
                                                <small class="text-muted">Date to remind before check date</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Check Photo</label>
                                                <input type="file" name="checks[0][check_photo]" class="form-control" accept="image/*">
                                                <small class="text-muted">Upload check image (max 2MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <strong>Total Check Amount: </strong><span id="totalCheckAmount">0.00</span>
                                <br>
                                <strong>Remaining Due: </strong><span id="checkRemainingDue" class="fw-bold text-danger">0.00</span>
                            </div>
                        </div>

                        <div class="col-12" id="ttFields" style="display: none;">
                            <div class="card border border-info">
                                <div class="card-header bg-info text-dark py-2">
                                    <h6 class="mb-0"><i class="bi bi-bank"></i> TT Payment Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6" style="position: relative;">
                                            <label for="ttBankSearch" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" id="ttBankSearch" class="form-control" placeholder="Search bank name..." autocomplete="off">
                                                <input type="hidden" name="tt_bank_name" id="ttBankNameInput">
                                            </div>
                                            <div id="ttBankResults" class="list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tt_account_no" class="form-label">Account No <span class="text-danger">*</span></label>
                                            <input type="text" name="tt_account_no" id="tt_account_no" class="form-control @error('tt_account_no') is-invalid @enderror" value="{{ old('tt_account_no') }}">
                                            @error('tt_account_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tt_amount" class="form-label">TT Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="0.01" name="tt_amount" id="tt_amount" class="form-control @error('tt_amount') is-invalid @enderror" value="{{ old('tt_amount') }}">
                                            </div>
                                            @error('tt_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tt_date" class="form-label">TT Date <span class="text-danger">*</span></label>
                                            <input type="date" name="tt_date" id="tt_date" class="form-control @error('tt_date') is-invalid @enderror" value="{{ old('tt_date', date('Y-m-d')) }}">
                                            @error('tt_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" id="cardFields" style="display: none;">
                            <div class="card border border-secondary">
                                <div class="card-header bg-secondary text-white py-2">
                                    <h6 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Card Payment Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="card_name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="card_name" id="card_name" class="form-control @error('card_name') is-invalid @enderror" value="{{ old('card_name') }}">
                                            @error('card_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="card_location" class="form-label">Location <span class="text-danger">*</span></label>
                                            <input type="text" name="card_location" id="card_location" class="form-control @error('card_location') is-invalid @enderror" value="{{ old('card_location') }}">
                                            @error('card_location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="card_amount" class="form-label">Card Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="0.01" name="card_amount" id="card_amount" class="form-control @error('card_amount') is-invalid @enderror" value="{{ old('card_amount') }}">
                                            </div>
                                            @error('card_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="card_date" class="form-label">Card Date <span class="text-danger">*</span></label>
                                            <input type="date" name="card_date" id="card_date" class="form-control @error('card_date') is-invalid @enderror" value="{{ old('card_date', date('Y-m-d')) }}">
                                            @error('card_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12" id="duePreview">
                            <div class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Due Amount:</strong> <span class="fs-5 fw-bold" id="calculatedDue">0.00</span>
                                    <small class="text-muted d-block">(Bill Amount - Discount - Payment Received)</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Net Payable</small>
                                    <span class="fs-4 fw-bold text-success" id="netPayable">0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Bill
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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

<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newCustomerForm" method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modal_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_mobile" class="form-label">Mobile</label>
                        <input type="text" name="mobile" id="modal_mobile" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="modal_location" class="form-label">Location</label>
                        <input type="text" name="location" id="modal_location" class="form-control">
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var banksSearchUrl = '{{ route("banks.search") }}';
    var customersSearchUrl = '{{ route("customers.search") }}';

    function setupSearch(input, results, routeUrl, onSelect) {
        if (!input || !results) return;

        var searchTimeout;

        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var term = this.value.trim();

            if (term.length < 2) {
                results.style.display = 'none';
                return;
            }

            results.innerHTML = '<div class="list-group-item text-muted text-center py-2">Searching...</div>';
            results.style.display = 'block';

            searchTimeout = setTimeout(function() {
                var url = routeUrl + '?term=' + encodeURIComponent(term);

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(function(res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function(data) {
                    results.innerHTML = '';

                    if (!Array.isArray(data) || data.length === 0) {
                        results.innerHTML = '<div class="list-group-item text-muted text-center py-2">No results found</div>';
                        return;
                    }

                    data.forEach(function(item) {
                        var el = document.createElement('a');
                        el.href = '#';
                        el.className = 'list-group-item list-group-item-action';
                        el.setAttribute('data-json', JSON.stringify(item));
                        el.textContent = item.name + (item.mobile ? ' - ' + item.mobile : '');
                        results.appendChild(el);
                    });
                })
                .catch(function(err) {
                    console.error('Search error:', err);
                    results.innerHTML = '<div class="list-group-item text-danger text-center py-2">Error loading results</div>';
                });
            }, 300);
        });

        results.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var item = e.target.closest('.list-group-item');
            if (!item || !item.hasAttribute('data-json')) return;
            var data = JSON.parse(item.getAttribute('data-json'));
            if (data) onSelect(data);
            results.style.display = 'none';
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        });
    }

    setupSearch(
        document.getElementById('customerSearch'),
        document.getElementById('customerResults'),
        customersSearchUrl,
        function(data) {
            document.getElementById('customerId').value = data.id;
            document.getElementById('customerSearch').value = data.name + (data.mobile ? ' - ' + data.mobile : '');
        }
    );

    setupSearch(
        document.getElementById('ttBankSearch'),
        document.getElementById('ttBankResults'),
        banksSearchUrl,
        function(data) {
            document.getElementById('ttBankNameInput').value = data.name;
            document.getElementById('ttBankSearch').value = data.name;
        }
    );

    // Payment type toggle
    var paymentType = document.getElementById('payment_type');
    var dueDateSection = document.getElementById('dueDateSection');
    var dueDateInput = document.getElementById('due_date');
    var checkFields = document.getElementById('checkFields');
    var ttFields = document.getElementById('ttFields');
    var cardFields = document.getElementById('cardFields');

    paymentType.addEventListener('change', function() {
        var val = this.value;
        dueDateSection.style.display = val === 'due' ? 'block' : 'none';
        dueDateInput.required = val === 'due';
        checkFields.style.display = val === 'check' ? 'block' : 'none';
        ttFields.style.display = val === 'tt' ? 'block' : 'none';
        cardFields.style.display = val === 'card' ? 'block' : 'none';
        updateDueCalculation();
    });

    var billAmount = document.getElementById('bill_amount');
    var discount = document.getElementById('discount');
    var paymentAmount = document.getElementById('payment_amount');

    function updateDueCalculation() {
        var bill = parseFloat(billAmount.value) || 0;
        var disc = parseFloat(discount.value) || 0;
        var paid = parseFloat(paymentAmount.value) || 0;
        var totalCheck = parseFloat(document.getElementById('totalCheckAmount').textContent) || 0;
        paid = paid + totalCheck;
        var due = bill - disc - paid;

        document.getElementById('calculatedDue').textContent = due.toFixed(2);
        document.getElementById('netPayable').textContent = (bill - disc).toFixed(2);

        if (due <= 0) {
            document.getElementById('calculatedDue').className = 'fs-5 fw-bold text-success';
            document.getElementById('calculatedDue').textContent = '0.00';
        } else {
            document.getElementById('calculatedDue').className = 'fs-5 fw-bold text-danger';
        }

        updateCheckRemainingDue();
    }

    [billAmount, discount, paymentAmount].forEach(function(el) {
        el.addEventListener('input', updateDueCalculation);
    });

    updateDueCalculation();
    paymentType.dispatchEvent(new Event('change'));

    // Dynamic check payments
    var checkIndex = 1;

    function setupBankSearchForCheck(input) {
        if (!input) return;
        var results = input.closest('.col-md-6').querySelector('.bank-results');
        if (!results) return;

        var searchTimeout;

        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var term = this.value.trim();

            if (term.length < 1) {
                results.style.display = 'none';
                return;
            }

            results.innerHTML = '<div class="list-group-item text-muted text-center py-2">Searching...</div>';
            results.style.display = 'block';

            searchTimeout = setTimeout(function() {
                var url = banksSearchUrl + '?term=' + encodeURIComponent(term);

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(function(res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function(data) {
                    results.innerHTML = '';

                    if (!Array.isArray(data) || data.length === 0) {
                        results.innerHTML = '<div class="list-group-item text-muted text-center py-2">No results found</div>';
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
                    results.innerHTML = '<div class="list-group-item text-danger text-center py-2">Error loading results</div>';
                });
            }, 250);
        });

        results.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var item = e.target.closest('.list-group-item');
            if (!item || !item.hasAttribute('data-json')) return;
            var data = JSON.parse(item.getAttribute('data-json'));
            if (data && data.name) {
                input.value = data.name;
            }
            results.style.display = 'none';
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.style.display = 'none';
            }
        });
    }

    document.getElementById('addCheckBtn').addEventListener('click', function() {
        var container = document.getElementById('checkPaymentsContainer');
        var newCheck = document.createElement('div');
        newCheck.className = 'card border border-warning mb-3 check-payment-item';
        newCheck.setAttribute('data-index', checkIndex);

        newCheck.innerHTML = `
            <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bank"></i> Check Payment #${checkIndex + 1}</span>
                <button type="button" class="btn btn-sm btn-danger remove-check-btn">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6" style="position: relative;">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="checks[${checkIndex}][bank_name]" class="form-control bank-search-input" placeholder="Search bank name..." autocomplete="off" required>
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="bank-results list-group position-absolute w-100 shadow search-dropdown" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; top: 100%; left: 0; background: #fff;"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check No <span class="text-danger">*</span></label>
                        <input type="text" name="checks[${checkIndex}][check_no]" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Check Date <span class="text-danger">*</span></label>
                        <input type="date" name="checks[${checkIndex}][check_date]" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Check Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" name="checks[${checkIndex}][check_amount]" class="form-control check-amount-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reminder Date</label>
                        <input type="date" name="checks[${checkIndex}][check_reminder_date]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Check Photo</label>
                        <input type="file" name="checks[${checkIndex}][check_photo]" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        `;

        container.appendChild(newCheck);
        setupBankSearchForCheck(newCheck.querySelector('.bank-search-input'));
        checkIndex++;
        updateCheckButtons();
        updateCheckTotal();
    });

    document.getElementById('checkPaymentsContainer').addEventListener('click', function(e) {
        if (e.target.closest('.remove-check-btn')) {
            e.target.closest('.check-payment-item').remove();
            updateCheckButtons();
            updateCheckTotal();
        }
    });

    function updateCheckButtons() {
        var items = document.querySelectorAll('.check-payment-item');
        items.forEach(function(item, index) {
            var btn = item.querySelector('.remove-check-btn');
            if (btn) {
                btn.style.display = items.length > 1 ? 'block' : 'none';
            }
            var header = item.querySelector('.card-header span');
            if (header) {
                header.innerHTML = '<i class="bi bi-bank"></i> Check Payment #' + (index + 1);
            }
        });
    }

    function updateCheckTotal() {
        var total = 0;
        document.querySelectorAll('.check-amount-input').forEach(function(input) {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('totalCheckAmount').textContent = total.toFixed(2);
        updateCheckRemainingDue();
        updateDueCalculation();
    }

    function updateCheckRemainingDue() {
        var bill = parseFloat(document.getElementById('bill_amount').value) || 0;
        var disc = parseFloat(document.getElementById('discount').value) || 0;
        var totalCheck = parseFloat(document.getElementById('totalCheckAmount').textContent) || 0;
        var otherPayment = parseFloat(document.getElementById('payment_amount').value) || 0;
        var remaining = bill - disc - totalCheck - otherPayment;
        document.getElementById('checkRemainingDue').textContent = remaining.toFixed(2);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('check-amount-input')) {
            updateCheckTotal();
        }
    });

    updateCheckButtons();
    setupBankSearchForCheck(document.querySelector('.bank-search-input'));

    // New Customer Form
    var newCustomerForm = document.getElementById('newCustomerForm');
    if (newCustomerForm) {
        var newCustomerModal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
        newCustomerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.id) {
                    document.getElementById('customerId').value = data.id;
                    document.getElementById('customerSearch').value = data.name + (data.mobile ? ' - ' + data.mobile : '');
                    newCustomerModal.hide();
                    newCustomerForm.reset();
                }
            })
            .catch(function(error) {
                console.error('Error creating customer:', error);
            });
        });
    }

    // New Bank Form
    var newBankForm = document.getElementById('newBankForm');
    if (newBankForm) {
        var newBankModal = new bootstrap.Modal(document.getElementById('newBankModal'));
        newBankForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    var bankName = formData.get('name');
                    document.querySelectorAll('.bank-search-input').forEach(function(input) {
                        if (input.value === '' || input.dataset.fromModal) {
                            input.value = bankName;
                            input.dataset.fromModal = 'true';
                        }
                    });
                    document.getElementById('ttBankNameInput').value = bankName;
                    document.getElementById('ttBankSearch').value = bankName;
                    newBankModal.hide();
                    newBankForm.reset();
                }
            })
            .catch(function(error) {
                console.error('Error creating bank:', error);
            });
        });
    }
});
</script>
@endsection