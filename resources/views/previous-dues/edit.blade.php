@extends('layouts.admin')

@section('title', 'Edit Previous Due')
@section('header', 'Edit Previous Due')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('previous-dues.index') }}">Previous Dues</a></li>
        <li class="breadcrumb-item"><a href="{{ route('previous-dues.show', $previousDue) }}">#{{ $previousDue->id }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('previous-dues.update', $previousDue) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $previousDue->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} @if($customer->mobile)({{ $customer->mobile }})@endif
                    </option>
                    @endforeach
                </select>
                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">৳</span>
                    <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount', $previousDue->amount) }}" required>
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="pending" {{ old('status', $previousDue->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ old('status', $previousDue->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $previousDue->notes) }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                <a href="{{ route('previous-dues.show', $previousDue) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
