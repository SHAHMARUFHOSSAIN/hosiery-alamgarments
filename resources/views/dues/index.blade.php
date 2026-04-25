@extends('layouts.admin')

@section('title', 'Dues')
@section('header', 'Dues')

@section('content')
<h2 class="mb-4">All Dues ({{ $dues->count() }})</h2>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Bill</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dues as $due)
                <tr>
                    <td>
                        <strong>{{ $due->customer->name ?? 'Unknown' }}</strong>
                        @if($due->customer->mobile)
                        <br><small>{{ $due->customer->mobile }}</small>
                        @endif
                    </td>
                    <td>{{ $due->bill->bill_no ?? 'N/A' }}</td>
                    <td class="text-danger fw-bold">${{ number_format($due->amount, 2) }}</td>
                    <td>{{ $due->due_date->format('M d, Y') }}</td>
                    <td>
                        @if($due->status == 'paid')
                        <span class="badge bg-success">Paid</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($due->status == 'pending')
                        <form method="POST" action="{{ route('dues.mark-paid', ['id' => $due->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Done</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4"><strong>No dues found</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection