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
                    <label for="bill_amount" class="form-label">Bill Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
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
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" name="discount" id="discount" 
                               class="form-control @error('discount') is-invalid @enderror" 
                               value="{{ old('discount', $bill->discount) }}">
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
@endsection