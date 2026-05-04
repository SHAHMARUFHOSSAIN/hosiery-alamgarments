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
        <form method="POST" action="{{ route('bills.update', $bill) }}">
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
                            <h6 class="mb-0"><i class="bi bi-bank"></i> Check Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" value="{{ $checkPayment->bank_name ?? 'N/A' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check No</label>
                                    <input type="text" class="form-control" value="{{ $checkPayment->check_no ?? 'N/A' }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Check Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="text" class="form-control" value="{{ number_format($checkPayment->check_amount, 2) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Check Date</label>
                                    <input type="date" class="form-control" value="{{ $checkPayment->check_date?->format('Y-m-d') }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reminder Date</label>
                                    <input type="date" class="form-control" value="{{ $checkPayment->check_reminder_date?->format('Y-m-d') }}" readonly>
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
                                    <label class="form-label">Check Photo</label>
                                    @if($checkPayment->check_photo)
                                    <div><a href="{{ asset('storage/' . $checkPayment->check_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Photo</a></div>
                                    @else
                                    <span class="text-muted">No photo uploaded</span>
                                    @endif
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