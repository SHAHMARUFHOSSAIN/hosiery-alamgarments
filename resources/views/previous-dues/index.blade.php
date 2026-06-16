@extends('layouts.admin')

@section('title', 'Previous Dues')
@section('header', 'Previous Dues')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <form method="GET" class="row g-2 align-items-end flex-grow-1">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="Search customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('previous-dues.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
        <a href="{{ route('previous-dues.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> New Previous Due
        </a>
    </div>
</div>

<h2 class="mb-4">All Previous Dues ({{ $previousDues->total() }})</h2>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previousDues as $pd)
                <tr>
                    <td>{{ $pd->id }}</td>
                    <td>
                        <a href="{{ route('previous-dues.show', $pd) }}" class="fw-semibold">
                            {{ $pd->customer->name ?? 'Unknown' }}
                        </a>
                        @if($pd->customer->mobile)
                        <br><small>{{ $pd->customer->mobile }}</small>
                        @endif
                    </td>
                    <td class="fw-bold text-danger">৳{{ number_format($pd->amount, 2) }}</td>
                    <td>
                        @if($pd->status == 'paid')
                        <span class="badge bg-success">Paid</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($pd->notes, 40) ?? '—' }}</td>
                    <td><small>{{ $pd->creator->name ?? 'N/A' }}</small></td>
                    <td><small>{{ $pd->created_at->format('M d, Y') }}</small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('previous-dues.show', $pd) }}" class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('previous-dues.edit', $pd) }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('previous-dues.destroy', $pd) }}" onsubmit="return confirm('Delete this previous due?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4"><strong>No previous dues found</strong></td></tr>
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
