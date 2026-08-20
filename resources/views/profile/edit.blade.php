@extends('layouts.admin')

@section('title', __('Profile'))

@section('header', __('Profile'))

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Profile Information') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone }}" placeholder="{{ __('e.g. 01711-111111') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Address') }}</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="{{ __('Your address') }}">{{ Auth::user()->address }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Role') }}</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->role === 'admin' ? __('Super Admin') : __('User') }}" disabled>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Change Password') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Current Password') }}</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('New Password') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
