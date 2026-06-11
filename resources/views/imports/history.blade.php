@extends('layouts.admin')

@section('title', 'Import History')
@section('header', 'Import History')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-primary fs-1 mb-1"><i class="bi bi-upload"></i></div>
                <h3 class="mb-0 fw-bold">{{ $stats['total_imports'] }}</h3>
                <small class="text-muted">Total Imports</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-success fs-1 mb-1"><i class="bi bi-database"></i></div>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_records']) }}</h3>
                <small class="text-muted">Total Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-info fs-1 mb-1"><i class="bi bi-plus-circle"></i></div>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_inserted']) }}</h3>
                <small class="text-muted">Records Inserted</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="text-warning fs-1 mb-1"><i class="bi bi-arrow-clockwise"></i></div>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_updated']) }}</h3>
                <small class="text-muted">Records Updated</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-people fs-1 text-primary"></i>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['customer_imports'] }}</div>
                    <small class="text-muted">Customer Imports</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-secondary border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-receipt fs-1 text-secondary"></i>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['bill_imports'] }}</div>
                    <small class="text-muted">Bill Imports</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-check-circle fs-1 text-info"></i>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['completed_count'] }}</div>
                    <small class="text-muted">Successful</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-star fs-1 text-success"></i>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['total_inserted'] + $stats['total_updated'] }}</div>
                    <small class="text-muted">Total Affected</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
        <i class="bi bi-funnel text-muted"></i>
        <form method="GET" action="{{ route('imports.history') }}" class="d-flex align-items-center gap-2 flex-wrap">
            @auth
            @if(auth()->user()->isAdmin())
            <label class="form-label mb-0 small text-nowrap">User:</label>
            <select name="user_id" class="form-select form-select-sm" style="width: 150px">
                <option value="">All Users</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            @endif
            @endauth
            <label class="form-label mb-0 small text-nowrap">Date:</label>
            <input type="date" name="date" class="form-control form-control-sm" style="width: 150px"
                   value="{{ request('date') }}">
            <label class="form-label mb-0 small text-nowrap">Type:</label>
            <select name="type" class="form-select form-select-sm" style="width: 120px">
                <option value="">All</option>
                <option value="customers" {{ request('type') === 'customers' ? 'selected' : '' }}>Customers</option>
                <option value="bills" {{ request('type') === 'bills' ? 'selected' : '' }}>Bills</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
            @if(request('date') || request('type') || request('user_id'))
            <a href="{{ route('imports.history') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i> Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Import Logs</h6>
        <a href="{{ route('imports.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-upload"></i> New Import
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>File</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Inserted</th>
                    <th>Updated</th>
                    <th>Skipped</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Errors</th>
                    <th>Data</th>
                    @if(auth()->user()->isAdmin())
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="small">
                        @if($log->import_date)
                        <span class="fw-semibold">{{ $log->import_date->format('M d, Y') }}</span>
                        <br><span class="text-muted">{{ $log->created_at->format('h:i A') }}</span>
                        @else
                        {{ $log->created_at->format('M d, Y h:i A') }}
                        @endif
                    </td>
                    <td class="small">{{ $log->file_name }}</td>
                    <td>
                        <span class="badge bg-{{ $log->import_type === 'customers' ? 'primary' : 'secondary' }}">
                            {{ ucfirst($log->import_type) }}
                        </span>
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $log->total_rows }}</span></td>
                    <td class="text-success fw-bold">{{ $log->inserted_rows }}</td>
                    <td class="text-info fw-bold">{{ $log->updated_rows }}</td>
                    <td class="text-warning fw-bold">{{ $log->skipped_rows }}</td>
                    <td class="small">{{ $log->user->name ?? 'N/A' }}</td>
                    <td>
                        @if($log->status === 'completed')
                        <span class="badge bg-success">Completed</span>
                        @elseif($log->status === 'processing')
                        <span class="badge bg-warning text-dark">Processing</span>
                        @elseif($log->status === 'failed')
                        <span class="badge bg-danger">Failed</span>
                        @else
                        <span class="badge bg-secondary">{{ ucfirst($log->status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($log->errors && count($log->errors) > 0)
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                data-bs-toggle="modal" data-bs-target="#errorsModal{{ $log->id }}">
                            <i class="bi bi-exclamation-triangle"></i> {{ count($log->errors) }}
                        </button>
                        @else
                        <span class="text-muted small">None</span>
                        @endif
                    </td>
                    <td>
                        @if($log->imported_data && count($log->imported_data['rows']) > 0)
                        <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" data-bs-toggle="modal" data-bs-target="#dataModal{{ $log->id }}">
                            <i class="bi bi-table"></i> View
                        </button>
                        @else
                        <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    @if(auth()->user()->isAdmin())
                    <td>
                        <form action="{{ route('imports.destroy', $log) }}" method="POST"
                              onsubmit="return confirm('Delete this import log? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="{{ auth()->user()->isAdmin() ? 12 : 11 }}" class="text-center py-5 text-muted">No imports found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-center">
            {!! $logs->links() !!}
        </div>
    </div>
    @endif
</div>

@foreach($logs as $log)
@if($log->errors && count($log->errors) > 0)
<div class="modal fade" id="errorsModal{{ $log->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Errors - {{ $log->file_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>#</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($log->errors as $index => $error)
                            <tr>
                                <td class="text-muted small">{{ $index + 1 }}</td>
                                <td class="text-danger small">{{ $error }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@if($log->imported_data && count($log->imported_data['rows']) > 0)
<div class="modal fade" id="dataModal{{ $log->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-table"></i>
                    {{ ucfirst($log->import_type) }} Data - {{ $log->file_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light position-sticky top-0">
                            <tr>
                                <th>#</th>
                                @foreach($log->imported_data['headings'] as $heading)
                                <th>{{ str_replace('_', ' ', ucfirst($heading)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($log->imported_data['rows'] as $index => $row)
                            <tr>
                                <td class="text-muted small">{{ $index + 1 }}</td>
                                @foreach($log->imported_data['headings'] as $heading)
                                <td class="small">{{ $row[$heading] ?? '' }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($log->imported_data['total_valid'] > 50)
            <div class="card-footer bg-white text-center text-muted small">
                Showing 50 of {{ $log->imported_data['total_valid'] }} valid rows
            </div>
            @endif
            <div class="modal-footer">
                <span class="text-muted small me-auto">
                    {{ $log->imported_data['total_valid'] }} valid row(s) imported on {{ $log->created_at->format('M d, Y h:i A') }}
                </span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection