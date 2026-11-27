<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Documents')</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .challan-title td {
            font-size: 16px;
            font-weight: bold;
            background-color: #002364;
            color: #fff;
            padding: 8px;
        }

        .header-row td {
            padding: 10px 5px;
            vertical-align: top;
        }

        .company-info {
            width: auto;
            margin-left: auto;
            border-collapse: collapse;
        }

        .company-info td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-label {
            text-align: left;
            font-weight: normal;
        }

        .info-colon {
            width: 15px;
            text-align: center;
        }

        .info-value {
            text-align: left;
        }

        .pickup-line {
            font-size: 14px;
            color: #548dd4;
            margin-top: 6px;
        }


        .footer-section {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-section td {
            vertical-align: top;
            padding: 10px 5px;
        }

        .signature-box {
            width: 240px;
            margin-left: auto;
            text-align: right;
        }

        .signature-img-container {
            height: 50px;
            margin-bottom: 2px;
            text-align: right;
        }

        .signature-img {
            display: inline-block;
            width: 100px;
            height: auto;
            max-height: 48px;
        }

        .signature-line {
            width: 100%;
            border-bottom: 1px solid #000;
            margin-top: 2px;
        }

        .signature-caption {
            text-align: right;
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 0;
        }

        .delivery-through {
            text-align: right;
            margin-top: 5px;
        }



        .signature-area {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-top: 6px;
            text-align: right;
        }
    </style>
    @stack('styles')
</head>

<body>
    <table class="table">
        <thead>
            <tr class="text-center challan-title">
                <td colspan="2">@yield('title')</td>
            </tr>
        </thead>
    </table>
    <table class="table">
        <thead>
            <tr class="header-row">
                <td class="text-left" style="width: 45%;">
                    <div style="width: 200px; margin-bottom: 4px;">
                        <img src="{{ public_path('assets/images/logo/logo.png') }}" alt="logo"
                            style="width: 100%; height: auto; display: block;">
                    </div>
                    <p class="pickup-line">We deal in all types of General items like Safety equipments, Electronics,
                        Machinary tools, Rubber and Plastic goods, Bearings, Cleaning equipments, Office stationary and
                        all types of Steel items etc.</p>
                </td>

                <td style="width: 55%;">
                    <table class="company-info">
                        <tr>
                            <td class="info-label">STRN</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>3277876227605</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">NTN</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>#AO64181</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Address</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">
                                <strong>Shop # 2, Deewan Street near Watan<br>
                                    High School, Brandreth Road,<br>
                                    Lahore, Punjab 54000, Pakistan</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Phone</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>+92349-4794955</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Fax</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>+9248-4720280</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Email</td>
                            <td class="info-colon">:</td>
                            <td class="info-value"><strong>tahiraminenterprises@gmail.com</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
    </table>
    @yield('content')
</body>

</html>
