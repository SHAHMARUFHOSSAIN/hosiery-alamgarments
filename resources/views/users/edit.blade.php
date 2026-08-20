@extends('layouts.admin')

@section('title', __('Edit User'))

@section('header', __('Edit User'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Edit') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('Edit User') }}</h2>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('Back') }}
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input type="password" name="password" id="password" 
                           class="form-control @error('password') is-invalid @enderror">
                    <small class="text-muted">{{ __('Leave blank to keep current password') }}</small>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Update User') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
