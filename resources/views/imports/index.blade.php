@extends('layouts.admin')

@section('title', 'Import Data')
@section('header', 'Import Management')

@section('content')
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="new-import-tab" data-bs-toggle="tab" data-bs-target="#new-import" type="button" role="tab">
            <i class="bi bi-upload"></i> New Import
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="previous-data-tab" data-bs-toggle="tab" data-bs-target="#previous-data" type="button" role="tab">
            <i class="bi bi-clock-history"></i> Previous Data
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="new-import" role="tabpanel">
        <div class="row">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-upload"></i> Upload Excel File</h6>
                    </div>
                    <div class="card-body">
                        <form id="importForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Import Type <span class="text-danger">*</span></label>
                                <select name="import_type" id="importType" class="form-select" required>
                                    <option value="">Select Type...</option>
                                    <option value="customers">Customers</option>
                                    <option value="bills">Bills</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Import Date</label>
                                <input type="date" name="import_date" id="importDate" class="form-control"
                                       value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Excel File <span class="text-danger">*</span></label>
                                <input type="file" name="file" id="fileInput" class="form-control"
                                       accept=".xlsx,.xls,.csv" required>
                                <div class="form-text">Supported: .xlsx, .xls, .csv (Max 10MB)</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" id="previewBtn" class="btn btn-primary">
                                    <i class="bi bi-eye"></i> Preview Data
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h6>Sample Files</h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('imports.sample', 'customers') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i> Customer Sample
                            </a>
                            <a href="{{ route('imports.sample', 'bills') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i> Bill Sample
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Import Rules</h6>
                    </div>
                    <div class="card-body small">
                        <ul class="mb-0 ps-3">
                            <li class="mb-1">First row must contain column headings.</li>
                            <li class="mb-1"><strong>Customers:</strong> Name and Mobile are required. Existing customers are matched by <strong>Mobile</strong> and updated.</li>
                            <li class="mb-1"><strong>Bills:</strong> Bill No, Customer Name/Mobile, Bill Amount, and Payment Type are required. Existing bills are matched by <strong>Bill No</strong> and updated.</li>
                            <li class="mb-1">New customers are auto-created from Bill imports if not found.</li>
                            <li class="mb-1">All imports are logged for audit purposes.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div id="previewArea" style="display: none;">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-table"></i> Preview</h6>
                            <div id="previewStats" class="d-flex gap-3"></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" id="previewTable">
                                    <thead class="table-light position-sticky top-0">
                                        <tr id="previewHeader"></tr>
                                    </thead>
                                    <tbody id="previewBody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-center" id="previewFooter" style="display: none;">
                            <button type="button" id="confirmBtn" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Confirm Import
                            </button>
                            <button type="button" id="cancelBtn" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        </div>
                    </div>

                    <div id="errorArea" style="display: none;">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-danger text-white py-2">
                                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Rows with Errors</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light position-sticky top-0">
                                            <tr>
                                                <th>Row</th>
                                                <th>Errors</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody id="errorBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="loadingArea" style="display: none;" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <h5 id="loadingText">Processing...</h5>
                </div>

                <div id="resultArea" style="display: none;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0"><i class="bi bi-check-circle text-success"></i> Import Result</h6>
                        </div>
                        <div class="card-body text-center py-4" id="resultBody"></div>
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('imports.history') }}" class="btn btn-outline-primary">
                                <i class="bi bi-clock-history"></i> View Import History
                            </a>
                            <button type="button" id="importAnotherBtn" class="btn btn-outline-secondary">
                                <i class="bi bi-plus"></i> Import Another
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="previous-data" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0 fw-bold">{{ $stats['total_imports'] }}</h3>
                        <small>Total Imports</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total_records']) }}</h3>
                        <small>Total Records</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total_inserted']) }}</h3>
                        <small>Records Inserted</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total_updated']) }}</h3>
                        <small>Records Updated</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex align-items-center">
                        <i class="bi bi-people fs-5 me-2 text-primary"></i>
                        <h6 class="mb-0">Customer Imports</h6>
                        <span class="ms-auto badge bg-primary rounded-pill">{{ $stats['customer_imports'] }}</span>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="display-3 fw-bold text-primary">{{ $stats['customer_imports'] }}</div>
                        <p class="text-muted mb-0">import operations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex align-items-center">
                        <i class="bi bi-receipt fs-5 me-2 text-secondary"></i>
                        <h6 class="mb-0">Bill Imports</h6>
                        <span class="ms-auto badge bg-secondary rounded-pill">{{ $stats['bill_imports'] }}</span>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="display-3 fw-bold text-secondary">{{ $stats['bill_imports'] }}</div>
                        <p class="text-muted mb-0">import operations</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
                <i class="bi bi-funnel text-muted"></i>
                <form method="GET" action="{{ route('imports.index') }}" id="dateFilterForm" class="d-flex align-items-center gap-2 flex-wrap">
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
                    <a href="{{ route('imports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i> Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Recent Imports</h6>
                <a href="{{ route('imports.history', array_filter(['date' => request('date'), 'type' => request('type'), 'user_id' => request('user_id')])) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-right"></i> View All
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
                            <th>Data</th>
                            @if(auth()->user()->isAdmin())
                            <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
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
                            <td>{{ $log->total_rows }}</td>
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
                        <tr><td colspan="{{ auth()->user()->isAdmin() ? 11 : 10 }}" class="text-center py-4 text-muted">No imports found. Start by importing data in the <a href="#" onclick="document.getElementById('new-import-tab').click(); return false;">New Import</a> tab.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentLogs->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3 mb-0">No imported data yet</p>
            </div>
            @endif

            @foreach($recentLogs as $log)
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
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('date') || urlParams.has('type') || urlParams.has('user_id')) {
        var tab = document.getElementById('previous-data-tab');
        if (tab) tab.click();
    }

    const importForm = document.getElementById('importForm');
    const fileInput = document.getElementById('fileInput');
    const importType = document.getElementById('importType');
    const previewBtn = document.getElementById('previewBtn');
    const previewArea = document.getElementById('previewArea');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');
    const previewStats = document.getElementById('previewStats');
    const previewFooter = document.getElementById('previewFooter');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const loadingArea = document.getElementById('loadingArea');
    const loadingText = document.getElementById('loadingText');
    const resultArea = document.getElementById('resultArea');
    const resultBody = document.getElementById('resultBody');
    const errorArea = document.getElementById('errorArea');
    const errorBody = document.getElementById('errorBody');
    const importAnotherBtn = document.getElementById('importAnotherBtn');

    let previewData = null;

    importForm.addEventListener('submit', function (e) {
        e.preventDefault();
        previewData = null;

        var formData = new FormData(this);

        previewArea.style.display = 'none';
        errorArea.style.display = 'none';
        resultArea.style.display = 'none';
        loadingArea.style.display = 'block';
        loadingText.textContent = 'Parsing Excel file...';
        previewBtn.disabled = true;

        fetch('{{ route("imports.preview") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
        .then(function (resp) {
            loadingArea.style.display = 'none';
            previewBtn.disabled = false;

            if (resp.status !== 200) {
                alert('Error: ' + (resp.data.error || 'Failed to parse file'));
                return;
            }

            previewData = resp.data;
            renderPreview(resp.data);
        })
        .catch(function () {
            loadingArea.style.display = 'none';
            previewBtn.disabled = false;
            alert('Network error. Please try again.');
        });
    });

    function renderPreview(data) {
        previewHeader.innerHTML = '';
        previewBody.innerHTML = '';
        errorBody.innerHTML = '';
        errorArea.style.display = 'none';

        data.headings.forEach(function (h) {
            var th = document.createElement('th');
            th.textContent = h.replace(/_/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
            previewHeader.appendChild(th);
        });

        var visibleRows = data.valid_rows;
        if (visibleRows.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = data.headings.length;
            td.className = 'text-center py-4 text-muted';
            td.textContent = 'No valid rows to display';
            tr.appendChild(td);
            previewBody.appendChild(tr);
        } else {
            visibleRows.forEach(function (row) {
                var tr = document.createElement('tr');
                data.headings.forEach(function (h) {
                    var td = document.createElement('td');
                    td.className = 'small';
                    td.textContent = row[h] !== null && row[h] !== undefined ? row[h] : '';
                    tr.appendChild(td);
                });
                previewBody.appendChild(tr);
            });
        }

        var statsHtml = '';
        statsHtml += '<span class="badge bg-info fs-6">Total: ' + data.total_rows + '</span>';
        statsHtml += '<span class="badge bg-success fs-6">Valid: ' + data.valid_count + '</span>';
        if (data.error_count > 0) {
            statsHtml += '<span class="badge bg-danger fs-6">Errors: ' + data.error_count + '</span>';
        }
        previewStats.innerHTML = statsHtml;

        var missingAlert = document.getElementById('missingHeadingsAlert');
        if (data.missing_headings && data.missing_headings.length > 0) {
            if (!missingAlert) {
                missingAlert = document.createElement('div');
                missingAlert.id = 'missingHeadingsAlert';
                missingAlert.className = 'alert alert-warning mb-3';
                missingAlert.innerHTML = '<strong>Missing columns:</strong> The file is missing these expected headings: <span id="missingList"></span>. Row validation may fail.';
                document.getElementById('previewTable').parentNode.insertBefore(missingAlert, document.getElementById('previewTable'));
            }
            document.getElementById('missingList').textContent = data.missing_headings.join(', ');
            missingAlert.style.display = 'block';
        } else if (missingAlert) {
            missingAlert.style.display = 'none';
        }

        if (data.error_rows && data.error_rows.length > 0) {
            errorArea.style.display = 'block';
            data.error_rows.forEach(function (er) {
                var tr = document.createElement('tr');
                tr.className = 'table-danger';

                var tdRow = document.createElement('td');
                tdRow.textContent = er.row;
                tr.appendChild(tdRow);

                var tdErr = document.createElement('td');
                tdErr.className = 'small text-danger';
                tdErr.textContent = er.errors.join('; ');
                tr.appendChild(tdErr);

                var tdData = document.createElement('td');
                tdData.className = 'small text-muted';
                var parts = [];
                for (var k in er.data) {
                    if (er.data[k] !== null && er.data[k] !== '') {
                        parts.push(k + ': ' + er.data[k]);
                    }
                }
                tdData.textContent = parts.join(', ');
                tr.appendChild(tdData);

                errorBody.appendChild(tr);
            });
        }

        previewArea.style.display = 'block';
        previewFooter.style.display = data.valid_count > 0 ? 'block' : 'none';
    }

    confirmBtn.addEventListener('click', function () {
        if (!previewData || previewData.valid_count === 0) return;

        previewFooter.style.display = 'none';
        loadingArea.style.display = 'block';
        loadingText.textContent = 'Importing data...';

        fetch('{{ route("imports.confirm") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
        .then(function (resp) {
            loadingArea.style.display = 'none';
            previewArea.style.display = 'none';
            errorArea.style.display = 'none';

            if (resp.status !== 200) {
                alert('Import failed: ' + (resp.data.error || 'Unknown error'));
                return;
            }

            var d = resp.data;
            var html = '';

            if (d.errors && d.errors.length > 0) {
                html += '<div class="alert alert-warning small mb-3" style="max-height: 150px; overflow-y: auto; text-align: left;">';
                html += '<strong>Warnings:</strong><br>';
                d.errors.forEach(function (e) { html += '&bull; ' + e + '<br>'; });
                html += '</div>';
            }

            html += '<div class="display-6 text-success mb-2"><i class="bi bi-check-circle-fill"></i></div>';
            html += '<h5>Import Completed Successfully</h5>';
            html += '<div class="row mt-3 g-2 justify-content-center">';
            html += '<div class="col-auto"><div class="p-3 bg-light rounded"><strong class="d-block fs-4 text-primary">' + d.total + '</strong><small>Total Rows</small></div></div>';
            html += '<div class="col-auto"><div class="p-3 bg-success bg-opacity-10 rounded"><strong class="d-block fs-4 text-success">' + d.inserted + '</strong><small>Inserted</small></div></div>';
            html += '<div class="col-auto"><div class="p-3 bg-info bg-opacity-10 rounded"><strong class="d-block fs-4 text-info">' + d.updated + '</strong><small>Updated</small></div></div>';
            html += '<div class="col-auto"><div class="p-3 bg-warning bg-opacity-10 rounded"><strong class="d-block fs-4 text-warning">' + d.skipped + '</strong><small>Skipped</small></div></div>';
            html += '</div>';

            resultBody.innerHTML = html;
            resultArea.style.display = 'block';
            document.getElementById('new-import-tab').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(function () {
            loadingArea.style.display = 'none';
            alert('Network error during import.');
        });
    });

    cancelBtn.addEventListener('click', function () {
        previewData = null;
        previewArea.style.display = 'none';
        errorArea.style.display = 'none';
        importForm.reset();
    });

    importAnotherBtn.addEventListener('click', function () {
        resultArea.style.display = 'none';
        previewArea.style.display = 'none';
        errorArea.style.display = 'none';
        importForm.reset();
    });
});
</script>
@endsection