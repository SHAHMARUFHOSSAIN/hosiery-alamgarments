@extends('layouts.admin')

@section('title', 'Data Management')

@section('header', 'Data Management')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">Data Management</li>
    </ol>
</nav>
@endsection

@section('content')
<form method="GET" class="mb-4">
    <div class="d-flex align-items-center gap-2">
        <label class="me-2">Time Period:</label>
        <select name="days" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
            <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
            <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
            <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last 1 Year</option>
            <option value="0" {{ $days == 0 ? 'selected' : '' }}>All Time</option>
        </select>
    </div>
</form>

<ul class="nav nav-tabs mb-4" id="dataTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="bills-tab" data-bs-toggle="tab" data-bs-target="#bills" type="button">Bills</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button">Customers</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="dues-tab" data-bs-toggle="tab" data-bs-target="#dues" type="button">Dues</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="banks-tab" data-bs-toggle="tab" data-bs-target="#banks" type="button">Banks</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="checks-tab" data-bs-toggle="tab" data-bs-target="#checks" type="button">Cheques</button>
    </li>
</ul>

<div class="tab-content" id="dataTabContent">
    <div class="tab-pane fade show active" id="bills">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recent Bills</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>User</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                         <tr>
                            <td>{{ $bill->bill_no }}
                                @if($bill->edited_at)
                                    <span class="badge bg-warning text-dark ms-1" title="Edited by {{ $bill->editor?->name ?? 'Unknown' }}">Edited</span>
                                @endif
                            </td>
                            <td>{{ $bill->customer->name ?? 'N/A' }}</td>
                            <td>৳{{ number_format($bill->bill_amount, 2) }}</td>
                            <td>৳{{ number_format($bill->discount, 2) }}</td>
                            <td>{{ $bill->user->name ?? 'N/A' }}</td>
                            <td>{{ $bill->report_date?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editBill{{ $bill->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('settings.bills.delete', $bill) }}" class="d-inline" onsubmit="return confirm('Delete this bill?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3">No bills found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentBills->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $recentBills->links() !!}
            </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="customers">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recent Customers</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Location</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCustomers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->mobile ?? 'N/A' }}</td>
                            <td>{{ $customer->location ?? 'N/A' }}</td>
                            <td>{{ $customer->creator->name ?? 'N/A' }}</td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editCustomer{{ $customer->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('settings.customers.delete', $customer) }}" class="d-inline" onsubmit="return confirm('Delete this customer?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-3">No customers found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentCustomers->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $recentCustomers->links() !!}
            </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="dues">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recent Dues</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Bill No</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDues as $due)
                        <tr>
                            <td>{{ $due->customer->name ?? 'N/A' }}</td>
                            <td>{{ $due->bill->bill_no ?? 'N/A' }}</td>
                            <td>৳{{ number_format($due->amount, 2) }}</td>
                            <td>{{ $due->due_date->format('M d, Y') }}</td>
                            <td>
                                @if($due->status == 'paid')
                                <span class="badge bg-success">Paid</span>
                                @else
                                <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editDue{{ $due->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('settings.dues.delete', $due) }}" class="d-inline" onsubmit="return confirm('Delete this due?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-3">No dues found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentDues->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $recentDues->links() !!}
            </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="banks">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bank Management</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newBankModal">
                    <i class="bi bi-plus"></i> New Bank
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bank Name</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBanks as $bank)
                        <tr>
                            <td>{{ $bank->name }}</td>
                            <td>
                                @if($bank->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $bank->creator->name ?? 'N/A' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editBankModal{{ $bank->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('banks.destroy', $bank) }}" class="d-inline" onsubmit="return confirm('Delete this bank?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3">No banks found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentBanks->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $recentBanks->links() !!}
            </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="checks">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recent Cheque Payments</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bill No</th>
                            <th>Customer</th>
                            <th>Bank</th>
                            <th>Cheque No</th>
                            <th>Cheque Amount</th>
                            <th>Cheque Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentChecks as $check)
                        <tr>
                            <td>{{ $check->bill->bill_no ?? 'N/A' }}</td>
                            <td>{{ $check->bill->customer->name ?? 'N/A' }}</td>
                            <td>{{ $check->bank_name ?? 'N/A' }}</td>
                            <td>{{ $check->check_no ?? 'N/A' }}</td>
                            <td>৳{{ number_format($check->check_amount, 2) }}</td>
                            <td>{{ $check->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                @if($check->status === 'encashed')
                                <span class="badge bg-success">Encashed</span>
                                @else
                                <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>{{ $check->check_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ $check->check_reminder_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="text-end">
                                <a href="{{ route('bills.show', $check->bill) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($check->check_photo)
                                <a href="{{ route('cheque.show', $check->check_photo) }}" target="_blank" title="View cheque">
                                    <img src="{{ route('cheque.show', $check->check_photo) }}" alt="Cheque photo" class="rounded border" style="width: 60px; height: 32px; object-fit: cover;">
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-3">No cheques found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentChecks->hasPages())
            <div class="card-footer bg-white text-center">
                {!! $recentChecks->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>

@foreach($recentBills as $bill)
<div class="modal fade" id="editBill{{ $bill->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.bills.update', $bill) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bill No</label>
                        <input type="text" name="bill_no" class="form-control" value="{{ $bill->bill_no }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shop Name</label>
                        <input type="text" name="shop_name" class="form-control" value="{{ $bill->shop_name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bill Man</label>
                        <input type="text" name="bill_man" class="form-control" value="{{ $bill->bill_man }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="bill_amount" class="form-control" value="{{ $bill->bill_amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount</label>
                        <input type="number" step="0.01" name="discount" class="form-control" value="{{ $bill->discount }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($recentCustomers as $customer)
<div class="modal fade" id="editCustomer{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.customers.update', $customer) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="{{ $customer->mobile }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ $customer->location }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($recentDues as $due)
<div class="modal fade" id="editDue{{ $due->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Due</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.dues.update', $due) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ $due->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ $due->due_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $due->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $due->status == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="newBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('banks.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Bank</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($recentBanks as $bank)
<div class="modal fade" id="editBankModal{{ $bank->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('banks.update', $bank) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $bank->name }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $bank->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Bank</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection