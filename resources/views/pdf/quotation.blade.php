@extends('pdf-layouts.tahir-ameen-enterprises')
@section('title', 'Quotation By Email')
@section('content')
    @php
        $items = $quotation->items ?? collect();
        $hasPr = $items->contains(fn($item) => filled(trim((string) ($item->pr ?? ''))));
        $grandTotal = $items->sum(
            fn($item) => (float) ($item->line_total ??
                (float) ($item->quantity ?? 0) * (float) ($item->rate_per_unit ?? 0)),
        );
    @endphp

<div style="padding: 1rem">

    <table class="table" >
        <thead>

            <tr>
                <td class="text-center" style="font-size:20px; font-weight: 700;" colspan="2">Quotation info</td>
            </tr>

            <tr class="header-row">
                <td class="text-left" style="width: 45%; padding-top: 15px;">
                    <div style="margin-bottom: 1rem">
                        <p><strong>Attn:</strong></p>
                        <div style="padding-left: 1.9rem">
                            <p>Procurement Officer</p>
                            <p><strong>{{ $quotation->company->name ?? '—' }}</strong></p>
                            @if (!empty($quotation->company?->branch))
                                <p>{{ $quotation->company->branch }}</p>
                            @endif
                            @if (!empty($quotation->company?->address))
                                <p style="width: 220px; word-wrap: break-word;">
                                    <strong>{{ $quotation->company->address }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p><strong>Dear Sir,</strong></p>
                        <div style="padding-left: 3.4rem">
                            <p>With reference to your email inquiry,</p>
                            <p>we take pleasure to quote the following items.</p>
                        </div>
                    </div>
                </td>

                <td style="width: 55%; padding-top: 5px;">
                    <table class="company-info">
                        <tr>
                            <td class="info-label">Quotation #</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>{{ $quotation->quotation_number ?? $quotation->id }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Quotation Date</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">
                                <strong>{{ $quotation->date ? \Illuminate\Support\Carbon::parse($quotation->date)->format('d/m/Y') : '—' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Quotes Validity</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">
                                <strong>{{ $quotation->validity ? $quotation->validity . ' Days' : '—' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Delivery Period</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>7 Days</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Payment Terms</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>Net 90 Days</strong></td>
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

    <table class="table" style="border: 1px solid #000; margin-top: 15px; text-align: center; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #000; padding: 8px;">{{ $hasPr ? 'PR#' : '#Sr. No' }}</th>
                <th style="border: 1px solid #000; padding: 8px;">Items Description</th>
                <th style="border: 1px solid #000; padding: 8px;">Unit</th>
                <th style="border: 1px solid #000; padding: 8px;">Quantity</th>
                <th style="border: 1px solid #000; padding: 8px;">Rate per Unit</th>
                <th style="border: 1px solid #000; padding: 8px;">Line Total PKR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $qty = (float) ($item->quantity ?? 0);
                    $rate = (float) ($item->rate_per_unit ?? 0);
                    $lineTotal = (float) ($item->line_total ?? $qty * $rate);
                @endphp
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $hasPr ? ($item->pr ?: '-') : $loop->iteration }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $item->description }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $item->unit ?? 'Each' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $qty }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ number_format($rate, 2) }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ number_format($lineTotal, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border: 1px solid #000; padding: 12px;">
                        No items found for this quotation.
                    </td>
                </tr>
            @endforelse

            <tr>
                <td colspan="2" class="text-left"
                    style="border: 1px solid #000; padding: 8px; background: #c6d9f0; font-weight: 700;">
                    These rates are Freight and Sales Tax exclusive
                </td>
                <td colspan="3" style="border: 1px solid #000; padding: 8px; background: #c6d9f0; font-weight: 700;">
                    Total Amount PKR
                </td>
                <td style="border: 1px solid #000; padding: 8px; background: #c6d9f0; font-weight: 700;">
                    {{ number_format($grandTotal, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="footer-section">
        <tr>
            <td style="width: 100%;" class="text-left">
                <p style="margin-bottom: 10px;">We have provided you the best reasonable quotes for the
                    above items and we are sure that you will consider them at a high rank.</p>
                <p>Thanking and Assuring you of our best services at all times.</p>
                <p style="font-weight: bold; margin-bottom: 10px;">Tahir Amin</p>
                <p>Direct: +92349-4794955</p>
                <p style="font-weight: bold">This Quotation is computer generated and does not need
                    Signature.</p>
            </td>
        </tr>
    </table>
</div>
@endsection
