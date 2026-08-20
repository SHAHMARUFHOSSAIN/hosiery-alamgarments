@extends('layouts.admin')

@section('title', __('Create Previous Due'))
@section('header', __('Create Previous Due'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('previous-dues.index') }}">{{ __('Previous Dues') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Create') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('previous-dues.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                    <option value="">{{ __('Select Customer') }}</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} @if($customer->mobile)({{ $customer->mobile }})@endif
                    </option>
                    @endforeach
                </select>
                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">৳</span>
                    <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount') }}" placeholder="0.00" required>
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                          placeholder="{{ __('Optional notes...') }}">{{ old('notes') }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> {{ __('Create') }}</button>
                <a href="{{ route('previous-dues.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
