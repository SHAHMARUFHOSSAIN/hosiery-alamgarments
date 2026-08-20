@extends('layouts.admin')

@section('title', __('Previous Dues'))
@section('header', __('Previous Dues'))

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <form method="GET" class="row g-2 align-items-end flex-grow-1">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="{{ __('Search customer...') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> {{ __('Filter') }}</button>
                <a href="{{ route('previous-dues.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
            </div>
        </form>
        <a href="{{ route('previous-dues.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> {{ __('New Previous Due') }}
        </a>
    </div>
</div>

<h2 class="mb-4">{{ __('All Previous Dues') }} ({{ $previousDues->total() }})</h2>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Notes') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previousDues as $pd)
                <tr>
                    <td>{{ $pd->id }}</td>
                    <td>
                        <a href="{{ route('previous-dues.show', $pd) }}" class="fw-semibold">
                            {{ $pd->customer->name ?? __('Unknown') }}
                        </a>
                        @if($pd->customer->mobile)
                        <br><small>{{ $pd->customer->mobile }}</small>
                        @endif
                    </td>
                    <td class="fw-bold text-danger">{{ format_currency($pd->amount) }}</td>
                    <td>
                        @if($pd->status == 'paid')
                        <span class="badge bg-success">{{ __('Paid') }}</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($pd->notes, 40) ?? '—' }}</td>
                    <td><small>{{ $pd->creator->name ?? 'N/A' }}</small></td>
                    <td><small>{{ $pd->created_at->format('M d, Y') }}</small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('previous-dues.show', $pd) }}" class="btn btn-sm btn-info" title="{{ __('View') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('previous-dues.edit', $pd) }}" class="btn btn-sm btn-primary" title="{{ __('Edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('previous-dues.destroy', $pd) }}" onsubmit="return confirm(__('Delete this previous due?'))">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4"><strong>{{ __('No previous dues found') }}</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($previousDues->hasPages())
    <div class="card-footer bg-white text-center">
        {!! $previousDues->appends(request()->only('status', 'search'))->links() !!}
    </div>
    @endif
</div>
@endsection
