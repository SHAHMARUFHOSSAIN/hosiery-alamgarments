@extends('layouts.admin')

@section('title', __('Edit Customer'))

@section('header', __('Edit Customer'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('Customers') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Edit') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('Edit Customer') }}</h2>
    <a href="{{ route('customers.index', ['page' => request('page', 1), 'search' => request('search'), 'user_id' => request('user_id'), 'location' => request('location')]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Back') }}
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="page" value="{{ request('page', 1) }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            <input type="hidden" name="location" value="{{ request('location') }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $customer->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="mobile" class="form-label">{{ __('Mobile') }}</label>
                    <input type="text" name="mobile" id="mobile" 
                           class="form-control @error('mobile') is-invalid @enderror" 
                           value="{{ old('mobile', $customer->mobile) }}">
                    @error('mobile')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label">{{ __('Location') }}</label>
                    <input type="text" name="location" id="location" 
                           class="form-control @error('location') is-invalid @enderror" 
                           value="{{ old('location', $customer->location) }}">
                    @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="opening_balance" class="form-label">{{ __('Opening Balance') }} <small class="text-muted">({{ __('previous due') }})</small></label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" min="0" name="opening_balance" id="opening_balance" 
                               class="form-control @error('opening_balance') is-invalid @enderror" 
                               value="{{ old('opening_balance', $customer->opening_balance) }}">
                        @error('opening_balance')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Update Customer') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
