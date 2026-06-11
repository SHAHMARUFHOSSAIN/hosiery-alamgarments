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
<div class="d-flex justify-content-between align-items-center mb-4">
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
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="shop_name" class="form-label">Shop Name</label>
                    <input type="text" name="shop_name" id="shop_name" 
                           class="form-control @error('shop_name') is-invalid @enderror" 
                           value="{{ old('shop_name', $bill->shop_name) }}">
                    @error('shop_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="bill_man" class="form-label">Bill Man</label>
                    <input type="text" name="bill_man" id="bill_man" 
                           class="form-control @error('bill_man') is-invalid @enderror" 
                           value="{{ old('bill_man', $bill->bill_man) }}">
                    @error('bill_man')
                    <div class="invalid-feedback">{{ $message }}</div>
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
                    $checkPayment = $bill->payments()->where('payment_type', 'check')->first();
                @endphp
                @if($checkPayment)
                <div class="col-12">
                    <div class="card border border-warning">
                        <div class="card-header bg-warning text-dark py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> Cheque Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="check_bank_name" class="form-label">Bank Name</label>
                                    <input type="text" name="check_bank_name" id="check_bank_name" class="form-control" value="{{ old('check_bank_name', $checkPayment->bank_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="check_no" class="form-label">Cheque No</label>
                                    <input type="text" name="check_no" id="check_no" class="form-control @error('check_no') is-invalid @enderror" value="{{ old('check_no', $checkPayment->check_no) }}">
                                    @error('check_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="check_amount" class="form-label">Cheque Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="check_amount" id="check_amount" class="form-control @error('check_amount') is-invalid @enderror" value="{{ old('check_amount', $checkPayment->check_amount) }}">
                                    </div>
                                    @error('check_amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="check_date" class="form-label">Cheque Date</label>
                                    <input type="date" name="check_date" id="check_date" class="form-control @error('check_date') is-invalid @enderror" value="{{ old('check_date', $checkPayment->check_date?->format('Y-m-d')) }}">
                                    @error('check_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="check_reminder_date" class="form-label">Reminder Date</label>
                                    <input type="date" name="check_reminder_date" id="check_reminder_date" class="form-control" value="{{ old('check_reminder_date', $checkPayment->check_reminder_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <div>
                                        @if($checkPayment->status === 'encashed')
                                        <span class="badge bg-success">Encashed</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Photo</label>
                                    @if($checkPayment->check_photo)
                                    <div class="mb-2"><a href="{{ asset('storage/' . $checkPayment->check_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Photo</a></div>
                                    @else
                                    <span class="text-muted">No photo uploaded</span>
                                    @endif
                                    <input type="file" name="check_photo" id="check_photo" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Bill
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection