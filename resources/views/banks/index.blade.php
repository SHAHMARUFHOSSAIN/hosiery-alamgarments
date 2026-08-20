@extends('layouts.admin')

@section('title', __('Bank Management'))

@section('header', __('Bank Management'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Banks') }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ __('Bank Management') }}</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBankModal">
        <i class="bi bi-plus"></i> {{ __('New Bank') }}
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('Search bank name or branch...') }}" 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('banks.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Bank Name') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $bank)
                <tr>
                    <td>{{ $bank->id }}</td>
                    <td class="fw-semibold">{{ $bank->name }}</td>
                    <td>
                        @if($bank->is_active)
                        <span class="badge bg-success">{{ __('Active') }}</span>
                        @else
                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                        @endif
                    </td>
                    <td>{{ $bank->creator->name ?? 'N/A' }}</td>
                    <td>{{ $bank->created_at->format('M d, Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" 
                                data-bs-toggle="modal" data-bs-target="#editBankModal{{ $bank->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('banks.destroy', $bank) }}" 
                              class="d-inline"                               onsubmit="return confirm('{{ __("Delete this bank?") }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-3">{{ __('No banks found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($banks->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $banks->links() !!}
    </div>
    @endif
</div>

<div class="modal fade" id="newBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('New Bank') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('banks.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Bank Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Bank') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($banks as $bank)
<div class="modal fade" id="editBankModal{{ $bank->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Bank') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('banks.update', $bank) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Bank Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $bank->name }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $bank->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update Bank') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
