@extends('layouts.admin')

@section('title', 'Edit Bill')

@section('header', 'Edit Bill')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">Bills</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">Edit Bill</h2>
    <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('bills.update', $bill) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="bill_no" class="form-label">Bill No <span class="text-danger">*</span></label>
                    <input type="text" name="bill_no" id="bill_no" 
                           class="form-control @error('bill_no') is-invalid @enderror" 
                           value="{{ old('bill_no', $bill->bill_no) }}" required>
                    @error('bill_no')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="report_date" class="form-label">Report Date <span class="text-danger">*</span></label>
                    <input type="date" name="report_date" id="report_date" 
                           class="form-control @error('report_date') is-invalid @enderror" 
                           value="{{ old('report_date', $bill->report_date?->format('Y-m-d')) }}" required>
                    @error('report_date')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="shop_name" class="form-label">Shop Name</label>
                    <input type="text" name="shop_name" id="shop_name" 
                           class="form-control @error('shop_name') is-invalid @enderror" 
                           value="{{ old('shop_name', $bill->shop_name) }}">
                    @error('shop_name')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="bill_man" class="form-label">Bill Man</label>
                    <input type="text" name="bill_man" id="bill_man" 
                           class="form-control @error('bill_man') is-invalid @enderror" 
                           value="{{ old('bill_man', $bill->bill_man) }}">
                    @error('bill_man')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="bill_amount" class="form-label">Bill Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" name="bill_amount" id="bill_amount" 
                               class="form-control @error('bill_amount') is-invalid @enderror" 
                               value="{{ old('bill_amount', $bill->bill_amount) }}" required>
                    </div>
                    @error('bill_amount')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="discount" class="form-label">Discount</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" name="discount" id="discount" 
                               class="form-control @error('discount') is-invalid @enderror" 
                               value="{{ old('discount', $bill->discount) }}">
                    </div>
                </div>

                @php
                    $mainPayment = $bill->payments()->where('payment_type', '!=', 'cash')->first();
                    $cashPayment = $bill->payments()->where('payment_type', 'cash')->first();
                    $checkPayments = $bill->payments()->where('payment_type', 'check')->get();
                    $currentType = old('payment_type', $mainPayment?->payment_type ?? 'cash');
                @endphp
                <div class="col-md-6">
                    <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                        <option value="cash" {{ $currentType == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="check" {{ $currentType == 'check' ? 'selected' : '' }}>Cheque</option>
                        <option value="tt" {{ $currentType == 'tt' ? 'selected' : '' }}>TT</option>
                        <option value="card" {{ $currentType == 'card' ? 'selected' : '' }}>Reference Card</option>
                        <option value="due" {{ $currentType == 'due' ? 'selected' : '' }}>Due</option>
                    </select>
                    @error('payment_type')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="payment_amount" class="form-label">Payment Received</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" step="0.01" name="payment_amount" id="payment_amount" 
                               class="form-control" value="{{ old('payment_amount', $cashPayment?->amount ?? 0) }}">
                    </div>
                    <small class="text-muted">Amount received now (if any)</small>
                </div>
                <div class="col-md-6">
                    <label for="payment_details" class="form-label">Payment Details</label>
                    <input type="text" name="payment_details" id="payment_details" 
                           class="form-control" value="{{ old('payment_details', $mainPayment?->details) }}">
                </div>

                {{-- Due Date --}}
                <div class="col-md-6" id="due_date_field" style="{{ $currentType == 'due' ? '' : 'display: none;' }}">
                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" id="due_date" 
                           class="form-control @error('due_date') is-invalid @enderror" 
                           value="{{ old('due_date', optional($bill->dues()->first())->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d')) }}">
                    @error('due_date')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Check Fields --}}
                <div class="col-12" id="checkFields" style="{{ $currentType == 'check' ? '' : 'display: none;' }}">
                    @php $checkIndex = 0; @endphp
                    @forelse($checkPayments as $cp)
                    <div class="card border border-warning mb-3">
                        <div class="card-header bg-warning text-dark py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> Cheque Payment #{{ $loop->iteration }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="check_bank_name" class="form-control" value="{{ old('check_bank_name', $cp->bank_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cheque No <span class="text-danger">*</span></label>
                                    <input type="text" name="check_no" class="form-control" value="{{ old('check_no', $cp->check_no) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="check_amount" class="form-control" value="{{ old('check_amount', $cp->check_amount) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
                                    <input type="date" name="check_date" class="form-control" value="{{ old('check_date', $cp->check_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reminder Date</label>
                                    <input type="date" name="check_reminder_date" class="form-control" value="{{ old('check_reminder_date', $cp->check_reminder_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <div>
                                        @if($cp->status === 'encashed')
                                        <span class="badge bg-success">Encashed</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Photo</label>
                                    @if($cp->check_photo)
                                    <div class="mb-2">
                                        <a href="{{ route('cheque.show', $cp->check_photo) }}" target="_blank">
                                            <img src="{{ route('cheque.show', $cp->check_photo) }}" alt="Cheque" class="img-thumbnail" style="width: 150px; height: 75px; object-fit: cover;">
                                        </a>
                                    </div>
                                    @else
                                    <span class="text-muted">No photo uploaded</span>
                                    @endif
                                    <input type="file" name="check_photo" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    @php $checkIndex++; @endphp
                    @empty
                    <div class="card border border-warning mb-3">
                        <div class="card-header bg-warning text-dark py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> Cheque Payment</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="check_bank_name" class="form-control" value="{{ old('check_bank_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cheque No <span class="text-danger">*</span></label>
                                    <input type="text" name="check_no" class="form-control" value="{{ old('check_no') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="check_amount" class="form-control" value="{{ old('check_amount') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
                                    <input type="date" name="check_date" class="form-control" value="{{ old('check_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reminder Date</label>
                                    <input type="date" name="check_reminder_date" class="form-control" value="{{ old('check_reminder_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cheque Photo</label>
                                    <input type="file" name="check_photo" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>

                {{-- TT Fields --}}
                <div class="col-12" id="ttFields" style="{{ $currentType == 'tt' ? '' : 'display: none;' }}">
                    <div class="card border border-info">
                        <div class="card-header bg-info text-dark py-2">
                            <h6 class="mb-0"><i class="bi bi-bank"></i> TT Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tt_bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="tt_bank_name" id="tt_bank_name" class="form-control @error('tt_bank_name') is-invalid @enderror" value="{{ old('tt_bank_name', $mainPayment?->tt_bank_name) }}">
                                    @error('tt_bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tt_account_no" class="form-label">Account No <span class="text-danger">*</span></label>
                                    <input type="text" name="tt_account_no" id="tt_account_no" class="form-control @error('tt_account_no') is-invalid @enderror" value="{{ old('tt_account_no', $mainPayment?->tt_account_no) }}">
                                    @error('tt_account_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tt_amount" class="form-label">TT Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="tt_amount" id="tt_amount" class="form-control @error('tt_amount') is-invalid @enderror" value="{{ old('tt_amount', $mainPayment?->tt_amount) }}">
                                    </div>
                                    @error('tt_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tt_date" class="form-label">TT Date <span class="text-danger">*</span></label>
                                    <input type="date" name="tt_date" id="tt_date" class="form-control @error('tt_date') is-invalid @enderror" value="{{ old('tt_date', $mainPayment?->tt_date?->format('Y-m-d')) }}">
                                    @error('tt_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Fields --}}
                <div class="col-12" id="cardFields" style="{{ $currentType == 'card' ? '' : 'display: none;' }}">
                    <div class="card border border-secondary">
                        <div class="card-header bg-secondary text-white py-2">
                            <h6 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Reference Card Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="card_reference" class="form-label">Reference Card <span class="text-danger">*</span></label>
                                    <input type="text" name="card_reference" id="card_reference" class="form-control @error('card_reference') is-invalid @enderror" value="{{ old('card_reference', $mainPayment?->card_reference) }}">
                                    @error('card_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <input type="text" name="card_location" id="card_location" class="form-control @error('card_location') is-invalid @enderror" value="{{ old('card_location', $mainPayment?->card_location) }}">
                                    @error('card_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_amount" class="form-label">Card Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" name="card_amount" id="card_amount" class="form-control @error('card_amount') is-invalid @enderror" value="{{ old('card_amount', $mainPayment?->card_amount) }}">
                                    </div>
                                    @error('card_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="card_date" class="form-label">Card Date <span class="text-danger">*</span></label>
                                    <input type="date" name="card_date" id="card_date" class="form-control @error('card_date') is-invalid @enderror" value="{{ old('card_date', $mainPayment?->card_date?->format('Y-m-d')) }}">
                                    @error('card_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Bill
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.getElementById('payment_type')?.addEventListener('change', function() {
        var val = this.value;
        document.getElementById('due_date_field').style.display = val === 'due' ? '' : 'none';
        document.getElementById('checkFields').style.display = val === 'check' ? '' : 'none';
        document.getElementById('ttFields').style.display = val === 'tt' ? '' : 'none';
        document.getElementById('cardFields').style.display = val === 'card' ? '' : 'none';
    });
</script>
@endpush
@endsection