@extends('pdf-layouts.tahir-ameen-enterprises')
@section('title', 'Sales Tax Invoice')

@section('content')
    @php
        $items = $invoice->items ?? collect();
        $totalExcl = $items->sum(
            fn($item) => (float) ($item->amount_excluding_sale_tax ??
                (float) ($item->quantity ?? 0) * (float) ($item->price_per_unit ?? 0)),
        );
        $totalGst = $items->sum(fn($item) => (float) ($item->plus_gs_t18 ?? 0));
        $paymentDue = $items->sum(fn($item) => (float) ($item->line_total ?? 0));
    @endphp
    <div style="padding: 1rem">
        <table class="table">
            <thead>
                <tr>
                    <td class="text-center" style="font-size:20px; font-weight: 700;" colspan="4">
                        Invoice No: {{ $invoice->invoice_number ?? $invoice->id }}
                    </td>
                </tr>
                <tr class="header-row">
                    <td class="text-left" style="width: 45%; padding-top: 15px;">
                        <div style="margin-bottom: 1rem">
                            <p><strong>Bill To:</strong> </p>
                            <div style="padding-left: 2.5rem">
                                <p><strong>{{ $invoice->company->name ?? '—' }}</strong></p>
                                @if (!empty($invoice->company?->ntn))
                                    <p><strong>NTN {{ $invoice->company?->ntn ?? '' }}</strong></p>
                                @endif
                                @if (!empty($invoice->company?->strn))
                                    <p><strong>STRN {{ $invoice->company->strn ?? '' }}</strong></p>
                                @endif

                                <p><strong>
                                        Accounts Payable Section</strong></p>

                                {{-- @if (!empty($invoice->company?->branch))
                                <p><strong>{{ $invoice->company->branch }}</strong></p>
                            @endif --}}

                                @if (!empty($invoice->company?->address))
                                    <p style="width: 200px; word-wrap: break-word;">
                                        <strong>{{ $invoice->company->address }}</strong>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td style="width: 55%; padding-top: 5px;">
                        <table class="company-info">
                            <tr>
                                <td class="info-label">Invoice Date</td>
                                <td class="info-colon">:</td>
                                <td class="info-value">
                                    <strong>{{ $invoice->date ? \Illuminate\Support\Carbon::parse($invoice->date)->format('d/m/Y') : '—' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Po Number</td>
                                <td class="info-colon">:</td>
                                <td class="info-value"><strong>{{ $invoice->po_number ?? '—' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="info-label">Payment Terms:</td>
                                <td class="info-colon">:</td>
                                <td class="info-value"><strong>Net 90 Days</strong></td>
                            </tr>
                            <tr>
                                <td class="info-label">Pay Method:</td>
                                <td class="info-colon">:</td>
                                <td class="info-value"><strong>{{ $invoice->pay_method ?: '—' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="info-label">Vendor No</td>
                                <td class="info-colon">:</td>
                                <td class="info-value"><strong>101297337</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </thead>

        </table>
        <table class="table"
            style="border: 1px solid #000; margin-top: 15px; text-align: center; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="border: 1px solid #000; padding: 8px;">#Sr. No</th>
                    <th style="border: 1px solid #000; padding: 8px;">Items Description</th>
                    <th style="border: 1px solid #000; padding: 8px;">Unit</th>
                    <th style="border: 1px solid #000; padding: 8px;">Quantity</th>
                    <th style="border: 1px solid #000; padding: 8px;">Rate per Unit</th>
                    <th style="border: 1px solid #000; padding: 8px;">Amount Excluding Sales Tax</th>
                    <th style="border: 1px solid #000; padding: 8px;">Plus GST18 %</th>
                    <th style="border: 1px solid #000; padding: 8px;">Line Total PKR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $qty = (float) ($item->quantity ?? 0);
                        $rate = (float) ($item->price_per_unit ?? 0);
                        $excl = (float) ($item->amount_excluding_sale_tax ?? $qty * $rate);
                        $gst = (float) ($item->plus_gs_t18 ?? $excl * 0.18);
                        $lineTotal = (float) ($item->line_total ?? $excl + $gst);
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 8px;">{{ $loop->iteration }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ $item->description }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ $item->unit ?? 'Each' }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ $qty }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ number_format($rate, 2) }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ number_format($excl, 2) }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ number_format($gst, 2) }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">{{ number_format($lineTotal, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="border: 1px solid #000; padding: 12px;">
                            No items found for this invoice.
                        </td>
                    </tr>
                @endforelse

                <tr>
                    <td colspan="5"></td>
                    <td style="padding: 0.5rem; border: 1px solid #000; font-weight: 700;">Total</td>
                    <td style="padding: 0.5rem; border: 1px solid #000; font-weight: 700;">Total GST</td>
                    <td style="padding: 0.5rem; border: 1px solid #000; font-weight: 700;">Payment Due</td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                    <td style="padding: 0.5rem; border: 1px solid #000; font-weight: 700;">
                        {{ number_format($totalExcl, 2) }}
                    </td>
                    <td style="padding: 0.5rem; border: 1px solid #000; font-weight: 700;">
                        {{ number_format($totalGst, 2) }}
                    </td>
                    <td style="padding: 0.5rem; border: 1px solid #000; font-weight: 700;">
                        {{ number_format($paymentDue, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="footer-section">
            <tr>
                <td style="width: 50%;" class="text-left">
                    <p>Thanks for purchasing</p>
                    <p style="font-weight: bold">Tahir Amin</p>
                    <p>Direct: +92349-4794955</p>
                </td>
                <td style="width: 50%;">
                    <div class="signature-box">
                        @if (!empty($invoice->signature_file) && file_exists(public_path('storage/' . $invoice->signature_file)))
                            <div class="signature-img-container">
                                <img src="{{ public_path('storage/' . $invoice->signature_file) }}" alt="Signature"
                                    class="signature-img">
                            </div>
                        @endif
                        <p style="text-align: right; font-weight: bold; padding-top: 2rem">
                            {{ $invoice->signature_text ?? '' }}
                        </p>
                        <div class="signature-line"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection
