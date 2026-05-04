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
                            <div id="customerResults" class="list-group position-absolute w-50" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
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
                            <div class="card border border-warning">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h6 class="mb-0"><i class="bi bi-bank"></i> Check Payment Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6" style="position: relative;">
                                            <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" id="bankSearch" class="form-control" placeholder="Search bank name..." autocomplete="off">
                                                <input type="hidden" name="bank_name" id="bankNameInput">
                                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#newBankModal">
                                                    <i class="bi bi-plus"></i> New
                                                </button>
                                            </div>
                                            <div id="bankResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="check_no" class="form-label">Check No <span class="text-danger">*</span></label>
                                            <input type="text" name="check_no" id="check_no" class="form-control @error('check_no') is-invalid @enderror" value="{{ old('check_no') }}">
                                            @error('check_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="check_date" class="form-label">Check Date <span class="text-danger">*</span></label>
                                            <input type="date" name="check_date" id="check_date" class="form-control @error('check_date') is-invalid @enderror" value="{{ old('check_date') }}">
                                            @error('check_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="check_amount" class="form-label">Check Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="0.01" name="check_amount" id="check_amount" class="form-control @error('check_amount') is-invalid @enderror" value="{{ old('check_amount') }}">
                                            </div>
                                            @error('check_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="check_reminder_date" class="form-label">Reminder Date</label>
                                            <input type="date" name="check_reminder_date" id="check_reminder_date" class="form-control" value="{{ old('check_reminder_date') }}">
                                            <small class="text-muted">Date to remind before check date</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="check_photo" class="form-label">Check Photo</label>
                                            <input type="file" name="check_photo" id="check_photo" class="form-control" accept="image/*">
                                            <small class="text-muted">Upload check image (max 2MB)</small>
                                        </div>
                                    </div>
                                </div>
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
                                            <div id="ttBankResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
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

                        <div class="col-12" id="duePreview" style="display: none;">
                            <div class="alert alert-info mb-0">
                                <strong>Due Amount:</strong> $<span id="calculatedDue">0.00</span>
                                <small class="text-muted d-block">(Bill Amount - Discount - Payment Received)</small>
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
    var paymentType = document.getElementById('payment_type');
    var dueDateSection = document.getElementById('dueDateSection');
    var dueDateInput = document.getElementById('due_date');
    var duePreview = document.getElementById('duePreview');
    var calculatedDue = document.getElementById('calculatedDue');
    var checkFields = document.getElementById('checkFields');
    var ttFields = document.getElementById('ttFields');
    var cardFields = document.getElementById('cardFields');
    
    var billAmount = document.getElementById('bill_amount');
    var discount = document.getElementById('discount');
    var paymentAmount = document.getElementById('payment_amount');
    
    function updateDueCalculation() {
        if (paymentType.value === 'due') {
            var bill = parseFloat(billAmount.value) || 0;
            var disc = parseFloat(discount.value) || 0;
            var paid = parseFloat(paymentAmount.value) || 0;
            var due = bill - disc - paid;
            calculatedDue.textContent = due.toFixed(2);
        }
    }
    
    paymentType.addEventListener('change', function() {
        if (this.value === 'due') {
            dueDateSection.style.display = 'block';
            dueDateInput.required = true;
            duePreview.style.display = 'block';
            checkFields.style.display = 'none';
            ttFields.style.display = 'none';
        } else if (this.value === 'check') {
            dueDateSection.style.display = 'none';
            dueDateInput.required = false;
            duePreview.style.display = 'none';
            checkFields.style.display = 'block';
            ttFields.style.display = 'none';
        } else if (this.value === 'tt') {
            dueDateSection.style.display = 'none';
            dueDateInput.required = false;
            duePreview.style.display = 'none';
            checkFields.style.display = 'none';
            ttFields.style.display = 'block';
            cardFields.style.display = 'none';
        } else if (this.value === 'card') {
            dueDateSection.style.display = 'none';
            dueDateInput.required = false;
            duePreview.style.display = 'none';
            checkFields.style.display = 'none';
            ttFields.style.display = 'none';
            cardFields.style.display = 'block';
        } else {
            dueDateSection.style.display = 'none';
            dueDateInput.required = false;
            duePreview.style.display = 'none';
            checkFields.style.display = 'none';
            ttFields.style.display = 'none';
            cardFields.style.display = 'none';
        }
    });
    
    [billAmount, discount, paymentAmount].forEach(function(el) {
        el.addEventListener('input', updateDueCalculation);
    });

    paymentType.dispatchEvent(new Event('change'));

    var bankSearch = document.getElementById('bankSearch');
    var bankNameInput = document.getElementById('bankNameInput');
    var bankResults = document.getElementById('bankResults');
    var newBankForm = document.getElementById('newBankForm');
    var newBankModal = new bootstrap.Modal(document.getElementById('newBankModal'));
    
    var bankSearchTimeout;
    bankSearch.addEventListener('input', function() {
        clearTimeout(bankSearchTimeout);
        var term = this.value.trim();
        if (term.length < 1) {
            bankResults.style.display = 'none';
            return;
        }
        bankSearchTimeout = setTimeout(function() {
            fetch('{{ route("banks.search") }}?term=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                bankResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(function(b) {
                        var item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = b.name;
                        item.dataset.name = b.name;
                        bankResults.appendChild(item);
                    });
                    bankResults.style.display = 'block';
                } else {
                    bankResults.style.display = 'none';
                }
            });
        }, 200);
    });
    
    bankResults.addEventListener('click', function(e) {
        e.preventDefault();
        var item = e.target;
        bankNameInput.value = item.dataset.name;
        bankSearch.value = item.dataset.name;
        bankResults.style.display = 'none';
    });
    
    document.addEventListener('click', function(e) {
        if (!bankSearch.contains(e.target) && !bankResults.contains(e.target)) {
            bankResults.style.display = 'none';
        }
    });
    
    newBankForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                bankNameInput.value = formData.get('name');
                bankSearch.value = formData.get('name');
                newBankModal.hide();
                newBankForm.reset();
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Error creating bank');
        });
    });

    var ttBankSearch = document.getElementById('ttBankSearch');
    var ttBankNameInput = document.getElementById('ttBankNameInput');
    var ttBankResults = document.getElementById('ttBankResults');
    
    var ttBankSearchTimeout;
    ttBankSearch.addEventListener('input', function() {
        clearTimeout(ttBankSearchTimeout);
        var term = this.value.trim();
        if (term.length < 1) {
            ttBankResults.style.display = 'none';
            return;
        }
        ttBankSearchTimeout = setTimeout(function() {
            fetch('{{ route("banks.search") }}?term=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                ttBankResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(function(b) {
                        var item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = b.name;
                        item.dataset.name = b.name;
                        ttBankResults.appendChild(item);
                    });
                    ttBankResults.style.display = 'block';
                } else {
                    ttBankResults.style.display = 'none';
                }
            });
        }, 200);
    });
    
    ttBankResults.addEventListener('click', function(e) {
        e.preventDefault();
        var item = e.target;
        ttBankNameInput.value = item.dataset.name;
        ttBankSearch.value = item.dataset.name;
        ttBankResults.style.display = 'none';
    });
    
    document.addEventListener('click', function(e) {
        if (!ttBankSearch.contains(e.target) && !ttBankResults.contains(e.target)) {
            ttBankResults.style.display = 'none';
        }
    });

    var customerSearch = document.getElementById('customerSearch');
    var customerId = document.getElementById('customerId');
    var customerResults = document.getElementById('customerResults');
    var newCustomerForm = document.getElementById('newCustomerForm');
    var newCustomerModal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
    
    var searchTimeout;
    customerSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var term = this.value.trim();
        if (term.length < 1) {
            customerResults.style.display = 'none';
            return;
        }
        searchTimeout = setTimeout(function() {
            var url = '{{ route("customers.search") }}?term=' + encodeURIComponent(term);
            console.log('Searching:', url);
            fetch(url, {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { 
                console.log('Response:', res.status);
                return res.json(); 
            })
            .then(function(data) {
                console.log('Data:', data);
                customerResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(function(c) {
                        var item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = c.name + ' (' + (c.mobile || 'N/A') + ')';
                        item.dataset.id = c.id;
                        item.dataset.name = c.name;
                        item.dataset.mobile = c.mobile;
                        customerResults.appendChild(item);
                    });
                    customerResults.style.display = 'block';
                } else {
                    customerResults.style.display = 'none';
                }
            });
        }, 200);
    });
    
    customerResults.addEventListener('click', function(e) {
        e.preventDefault();
        var item = e.target;
        customerId.value = item.dataset.id;
        customerSearch.value = item.dataset.name + ' (' + (item.dataset.mobile || 'N/A') + ')';
        customerResults.style.display = 'none';
    });
    
    document.addEventListener('click', function(e) {
        if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.style.display = 'none';
        }
    });
    
    newCustomerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.id) {
                customerId.value = data.id;
                customerSearch.value = data.name + ' (' + (data.mobile || 'N/A') + ')';
                newCustomerModal.hide();
                newCustomerForm.reset();
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Error creating customer');
        });
    });
});
</script>
@endsection