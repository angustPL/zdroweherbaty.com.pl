<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        /* Reset styles */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        /* Main styles */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            background-color: #f6f6f6;
        }

        .content {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            background-color: #f8f9fa;
            padding: 30px 30px 20px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }

        .header img {
            max-width: 160px;
            height: auto;
        }

        .header h1 {
            margin: 20px 0 0;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }

        .body {
            background-color: #ffffff;
            padding: 0;
        }

        .inner-body {
            width: 100%;
            max-width: 570px;
            margin: 0 auto;
            padding: 30px;
        }

        .panel {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .panel h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #026941;
            font-size: 16px;
            font-weight: bold;
        }

        .panel p {
            margin: 5px 0;
            color: #333;
        }

        .panel strong {
            color: #000;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }

        /* Table styles */
        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .email-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
            color: #495057;
        }

        .email-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .email-table tr:last-child td {
            border-bottom: none;
        }

        .email-table .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .email-table .total-row td {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #026941;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
                padding: 20px !important;
            }

            .footer {
                width: 100% !important;
            }

            .email-table th,
            .email-table td {
                padding: 8px !important;
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body>

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">

                    <!-- Header -->
                    <tr>
                        <td class="header">
                            @if ($headerUrl ?? false)
                                <a href="{{ $headerUrl }}" style="display: inline-block;">
                            @endif

                            @if ($logo ?? false)
                                <img src="{{ $logo }}" alt="{{ config('app.name') }} Logo"
                                    style="max-width: 160px; height: auto;" />
                            @else
                                @php
                                    $logoPath = public_path('img/bifix-logo.png');
                                    if (file_exists($logoPath)) {
                                        $logoBase64 =
                                            'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                                    } else {
                                        $logoBase64 = null;
                                    }
                                @endphp
                                @if ($logoBase64)
                                    <img src="{{ $logoBase64 }}" alt="{{ config('app.name') }} Logo"
                                        style="max-width: 160px; height: auto;" />
                                @else
                                    <div
                                        style="background-color: #026941; color: white; font-size: 24px; font-weight: bold; padding: 15px 30px; border-radius: 8px; display: inline-block;">
                                        BIFIX
                                    </div>
                                @endif
                            @endif

                            @if ($headerUrl ?? false)
                                </a>
                            @endif

                            @if ($title ?? false)
                                <h1>{{ $title }}</h1>
                            @endif
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="body">
                            <table class="inner-body" align="center" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <tr>
                                    <td>
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            {{ $footer ?? '&copy; ' . date('Y') . ' ' . config('app.name') . '. ' . __('All rights reserved.') }}
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
