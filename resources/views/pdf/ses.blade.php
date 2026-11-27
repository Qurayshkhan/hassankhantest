@extends('pdf-layouts.tahir-ameen-enterprises')
@section('title', 'SES')
@section('content')
    @php
        $items = $ses->items ?? collect();
        $totalAmount = $items->sum(fn($item) => (float) ($item->amount ?? 0));
    @endphp
<div style="padding: 1rem">

    <table class="table" >
        <thead>
            <tr>
                <td class="text-center" style="font-size:20px; font-weight: 700;" colspan="4">
                    DOC NO: {{ $ses->document_number ?? '' }}
                </td>
            </tr>
            <tr class="header-row">
                <td class="text-left" style="width: 45%; padding-top: 15px;">
                    <div style="margin-bottom: 1rem">
                        <p><strong>Job Complete Certificate:</strong></p>
                        <div style="padding-left: 2.5rem">
                            <p>
                                <strong>{{ $ses->company->name ?? '—' }}</strong>
                            </p>
                            @if (!empty($ses->company?->branch))
                                <p>{{ $ses->company->branch }}</p>
                            @endif
                            @if (!empty($ses->company?->address))
                                <p style="width: 200px; word-wrap: break-word;">
                                    <strong>{{ $ses->company->address }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                </td>

                <td style="width: 55%; padding-top: 5px;">
                    <table class="company-info">
                        <tr>
                            <td class="info-label">Date</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">
                                <strong>{{ $ses->date ? \Illuminate\Support\Carbon::parse($ses->date)->format('m/d/Y') : '—' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Po Number</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">
                                <strong>{{ $ses->po_number ?? '—' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">RGP:</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>{{ $ses->rgp ?: '—' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">IGP:</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>{{ $ses->igp ?: '—' }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
    </table>
    <table class="table" style="border: 1px solid #000; margin-top: 15px; text-align: center; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #000; padding: 8px;">#Sr. No</th>
                <th style="border: 1px solid #000; padding: 8px;">Items Description</th>
                <th style="border: 1px solid #000; padding: 8px;">Unit</th>
                <th style="border: 1px solid #000; padding: 8px;">Quantity</th>
                <th style="border: 1px solid #000; padding: 8px;">Rate Per Unit</th>
                <th style="border: 1px solid #000; padding: 8px;">Line Total PKR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $loop->iteration }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $item->description }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $item->unit ?? 'Each' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $item->quantity }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ $item->rate }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px;">
                        {{ number_format((float) ($item->amount ?? 0), 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border: 1px solid #000; padding: 12px;">
                        No items found for this SES document.
                    </td>
                </tr>
            @endforelse

            @if ($items->isNotEmpty())
                <tr>
                    <td colspan="5"
                        style="border: 1px solid #000; padding: 8px; background: #f2f2f2; font-weight: 700; text-align: right;">
                        Total Amount
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; background: #f2f2f2; font-weight: 700;">
                        {{ number_format($totalAmount, 2) }}
                    </td>
                </tr>
            @endif
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
                    @if (!empty($ses->signature_file) && file_exists(public_path('storage/' . $ses->signature_file)))
                        <div class="signature-img-container">
                            <img src="{{ public_path('storage/' . $ses->signature_file) }}" alt="Signature"
                                class="signature-img">
                        </div>
                    @endif
                    <p style="text-align: right; font-weight: bold; padding-top: 2rem">
                        {{ $ses->signature_text ?? '' }}
                    </p>
                    <div class="signature-line"></div>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection
