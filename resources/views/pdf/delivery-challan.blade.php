@extends('pdf-layouts.tahir-ameen-enterprises')
@section('title', 'Delivery Challan')
@push('styles')
    <style>
        .footer-section {
            margin-top: 30px;
        }
    </style>
@endpush
@section('content')
    <div style="padding: 1rem">

        <table class="table">
            <thead>
                <tr class="header-row">
                    <td class="text-left" style="width: 45%; padding-top: 15px;">
                        <p><strong>Customer Name:</strong>
                            {{ $deliveryChallan?->company?->name }} ({{ $deliveryChallan?->company?->branch }})
                        </p>
                        <p>
                            <strong>Address: </strong>
                            {{ $deliveryChallan?->company?->address ?? '' }}
                        </p>
                    </td>

                    <td style="width: 55%; padding-top: 5px;">
                        <table class="company-info">
                            <tr>
                                <td class="info-label">Challan No</td>
                                <td class="info-colon">:</td>
                                <td class="info-value"><strong>{{ $deliveryChallan->challan_number ?? '' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="info-label">Delivery Date</td>
                                <td class="info-colon">:</td>
                                <td class="info-value">
                                    <strong>{{ \Carbon\Carbon::parse($deliveryChallan->delivery_date)->format('d/m/Y') }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">PO No</td>
                                <td class="info-colon">:</td>
                                <td class="info-value"><strong>{{ $deliveryChallan->po_number ?? '' }}</strong></td>
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
                </tr>
            </thead>
            <tbody>
                @if ($deliveryChallan?->items)
                    @foreach ($deliveryChallan->items as $index => $item)
                        <tr>
                            <td style="border: 1px solid #000; padding: 8px;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">{{ $item->description ?? '' }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px;">{{ $item->unit ?? '' }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">{{ $item->quantity ?? '' }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <table class="footer-section">
            <tr>
                <td style="width: 50%;" class="text-left">
                    <p style="margin-bottom: 4px;">Thanks for Purchasing</p>
                    <p style="font-weight: bold; margin-bottom: 2px;">Tahir Amin Enterprises</p>
                    <p>Direct: +92494794955</p>
                </td>

                <td style="width: 50%;">
                    <div class="signature-box">
                        <p style="text-align: right; margin-bottom: 32px;">Kindly acknowledge receipt.</p>

                        <div class="signature-area">
                            @if (!empty($deliveryChallan->signature_file) && file_exists(public_path($deliveryChallan->signature_file)))
                                <img src="{{ public_path($deliveryChallan->signature_file) }}" alt="sign"
                                    class="signature-img" />
                            @endif
                            @if (!empty($deliveryChallan->signature_text))
                                <p class="signature-caption ">{{ $deliveryChallan->signature_text }}</p>
                            @endif
                        </div>


                        <p class="delivery-through">Delivery Through</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection
