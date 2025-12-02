<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport generowania cache Enova</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #ddd;
        }

        .header {
            background-color: {{ $success ? '#026941' : '#dc3545' }};
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .status {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .content {
            margin-bottom: 20px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .stats-table th,
        .stats-table td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .stats-table th:first-child,
        .stats-table td:first-child {
            width: 60%;
            padding-right: 20px;
        }

        .stats-table th:last-child,
        .stats-table td:last-child {
            width: 40%;
            text-align: right;
            padding-left: 20px;
        }

        .stats-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .stats-table tr:hover {
            background-color: #f5f5f5;
        }

        .success {
            color: #026941;
            font-weight: bold;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
        }

        .duration {
            font-size: 18px;
            color: #666;
            margin-top: 20px;
        }

        .error-message {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <div class="status">
                @if ($success)
                    ✓ Cache został wygenerowany pomyślnie
                @else
                    ✗ Błąd podczas generowania cache
                @endif
            </div>
            <p>Raport generowania cache Enova</p>
        </div>

        <div class="content">
            @if (!$success && $errorMessage)
                <div class="error-message">
                    <strong>Błąd:</strong> {{ $errorMessage }}
                </div>
            @endif

            <h2>Statystyki</h2>
            <table class="stats-table">
                <tr>
                    <th>Element</th>
                    <th>Wartość</th>
                </tr>
                <tr>
                    <td>Produkty</td>
                    <td class="success">{{ number_format($stats['products'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>Pojedyncze produkty</td>
                    <td class="success">{{ number_format($stats['individual_products'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>Produkty w grupach</td>
                    <td class="success">{{ number_format($stats['products_by_group'] ?? 0) }} grup</td>
                </tr>
                <tr>
                    <td>Grupy</td>
                    <td class="success">
                        @if (($stats['groups'] ?? 0) > 0)
                            {{ number_format($stats['groups']) }}
                        @else
                            Tak (z hierarchii)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Opcje dostawy</td>
                    <td class="success">{{ number_format($stats['deliveries'] ?? 0) }}</td>
                </tr>
            </table>

            <div class="duration">
                <strong>⏱ Czas wykonania:</strong> {{ number_format($duration, 2) }} sekund
            </div>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                Raport wygenerowany automatycznie przez system cache Enova.<br>
                Data: {{ now()->format('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>

</html>

