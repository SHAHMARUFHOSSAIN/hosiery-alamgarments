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
                <form method="POST" action="{{ route('bills.store') }}">
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

                        <div class="col-md-4">
                            <label for="bill_amount" class="form-label">Bill Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="bill_amount" id="bill_amount" class="form-control @error('bill_amount') is-invalid @enderror" value="{{ old('bill_amount') }}" required>
                            </div>
                            @error('bill_amount')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="discount" class="form-label">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
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
                                <option value="due" {{ old('payment_type') == 'due' ? 'selected' : '' }}>Due</option>
                            </select>
                            @error('payment_type')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="payment_amount" class="form-label">Payment Received</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
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
            updateDueCalculation();
        } else {
            dueDateSection.style.display = 'none';
            dueDateInput.required = false;
            duePreview.style.display = 'none';
        }
    });
    
    [billAmount, discount, paymentAmount].forEach(function(el) {
        el.addEventListener('input', updateDueCalculation);
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