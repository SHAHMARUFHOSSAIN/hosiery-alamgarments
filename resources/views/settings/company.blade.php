@extends('layouts.admin')

@section('title', 'Company Settings')
@section('header', 'Company Settings')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">Company</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-building me-2 text-secondary"></i>Company Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings['company_name']) }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $settings['company_address']) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $settings['company_email']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Voucher Prefix</label>
                            <input type="text" name="voucher_prefix" class="form-control" value="{{ old('voucher_prefix', $settings['voucher_prefix']) }}">
                            <small class="text-muted">Prefix for auto-generated voucher numbers (e.g. V for V-202606-0001)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Logo</label>
                            <input type="file" name="company_logo" class="form-control" accept="image/*">
                            @if($settings['company_logo'])
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo" style="max-height: 60px;" class="border rounded p-1">
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Favicon</label>
                            <input type="file" name="company_favicon" class="form-control" accept="image/*">
                            @if($settings['company_favicon'])
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['company_favicon']) }}" alt="Favicon" style="max-height: 40px;" class="border rounded p-1">
                            </div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg"></i> Save Settings
                            </button>
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection