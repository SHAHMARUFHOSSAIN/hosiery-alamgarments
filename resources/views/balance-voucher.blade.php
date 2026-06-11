<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companyName }} - {{ $mainBalance->type === 'credit' ? 'Money Receipt' : 'Payment Voucher' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .voucher-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }
        .voucher {
            background: #fff;
            border: 2px solid #1a237e;
            border-radius: 10px;
            padding: 35px 40px;
            position: relative;
        }
        .voucher::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            border: 1px dashed #dee2e6;
            border-radius: 5px;
            pointer-events: none;
        }
        .voucher-header {
            text-align: center;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }
        .voucher-header h2 {
            color: #1a237e;
            font-weight: 900;
            margin-bottom: 4px;
            font-size: 30px;
        }
        .voucher-header .subtitle {
            color: #495057;
            font-size: 14px;
        }
        .voucher-type {
            display: inline-block;
            padding: 10px 36px;
            font-size: 20px;
            font-weight: 700;
            border-radius: 5px;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        .voucher-type.credit {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .voucher-type.debit {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .voucher-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 0 5px;
        }
        .voucher-meta .meta-item {
            font-size: 15px;
        }
        .voucher-meta .meta-item strong {
            display: inline-block;
            min-width: 100px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 24px;
        }
        .details-table td {
            padding: 8px 10px;
            vertical-align: top;
            font-size: 15px;
        }
        .details-table td.label {
            font-weight: 600;
            width: 150px;
            color: #495057;
        }
        .details-table td.separator {
            width: 15px;
            text-align: center;
        }
        .details-table td.value {
            font-weight: 500;
        }
        .amount-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 18px 24px;
            margin-bottom: 24px;
        }
        .amount-box .amount-figure {
            font-size: 32px;
            font-weight: 800;
            color: #1a237e;
        }
        .amount-box .amount-words {
            font-size: 15px;
            color: #6c757d;
            font-style: italic;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
        }
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid #dee2e6;
        }
        .signature-box {
            text-align: center;
            min-width: 180px;
        }
        .signature-box .line {
            width: 180px;
            border-top: 1px solid #333;
            margin: 48px auto 5px;
        }
        .signature-box .label {
            font-size: 13px;
            color: #6c757d;
        }
        .signature-box .name {
            font-weight: 600;
            font-size: 15px;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-print .btn {
            padding: 10px 30px;
            font-size: 16px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .voucher-wrapper {
                max-width: 100%;
            }
            .voucher {
                border: 2px solid #1a237e;
                padding: 30px 35px;
            }
            .voucher::before {
                top: 10px;
                left: 10px;
                right: 10px;
                bottom: 10px;
            }
            .no-print {
                display: none !important;
            }
            .signature-area {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="voucher-wrapper">
        <div class="no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Voucher
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="voucher">
            <div class="voucher-header">
                @php $logo = \App\Models\Setting::get('company_logo'); @endphp
                <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
                    @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo" style="max-height: 60px;">
                    @endif
                    <div>
                        <h2 class="mb-0">{{ $companyName }}</h2>
                    </div>
                </div>
                <div class="subtitle fw-semibold">{{ $mainBalance->user->name ?? '' }}</div>
                <div class="subtitle">{{ $mainBalance->user->address ?? 'N/A' }}</div>
                <div class="subtitle">
                    Phone: {{ $mainBalance->user->phone ?? 'N/A' }} &nbsp;|&nbsp; Email: {{ $mainBalance->user->email ?? 'N/A' }}
                </div>
                <div class="voucher-type {{ $mainBalance->type }}">
                    {{ $mainBalance->type === 'credit' ? 'MONEY RECEIPT' : 'PAYMENT VOUCHER' }}
                </div>
            </div>

            <div class="voucher-meta">
                <div class="meta-item">
                    <strong>Voucher No:</strong> {{ $mainBalance->voucher_no ?? 'N/A' }}
                </div>
                <div class="meta-item">
                    <strong>Date:</strong> {{ $mainBalance->created_at->format('d M Y, h:i A') }}
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td class="label">Received From / Paid To</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->party_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Transaction Name</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->name }}</td>
                </tr>
                @if($mainBalance->invoice_no)
                <tr>
                    <td class="label">Invoice No</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->invoice_no }}</td>
                </tr>
                @endif
                @if($mainBalance->reference)
                <tr>
                    <td class="label">Reference</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->reference }}</td>
                </tr>
                @endif
                @if($mainBalance->note)
                <tr>
                    <td class="label">Description</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->note }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Transaction Type</td>
                    <td class="separator">:</td>
                    <td class="value">
                        <span class="badge bg-{{ $mainBalance->type === 'credit' ? 'success' : 'danger' }}">
                            {{ ucfirst($mainBalance->type) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Recorded By</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Branch / User</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $mainBalance->branch->name ?? 'N/A' }}</td>
                </tr>
            </table>

            <div class="amount-box">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Amount in Figures</span>
                    <span class="amount-figure">৳ {{ number_format($mainBalance->amount, 2) }}</span>
                </div>
                @php
                    use App\Helpers\NumberHelper;
                    $words = NumberHelper::toWords($mainBalance->amount);
                @endphp
                <div class="amount-words">
                    <strong>Amount in Words:</strong> {{ $words }}
                </div>
            </div>

            <div class="signature-area">
                <div class="signature-box">
                    <div class="line"></div>
                    <div class="name">{{ $mainBalance->user->name ?? '' }}</div>
                    <div class="label">Received By</div>
                </div>
                <div class="signature-box">
                    <div class="line"></div>
                    <div class="name">{{ $mainBalance->party_name ?? '_______________' }}</div>
                    <div class="label">Party's Signature</div>
                </div>
                <div class="signature-box">
                    <div class="line"></div>
                    <div class="name">Authorized</div>
                    <div class="label">Authorized Signature</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
