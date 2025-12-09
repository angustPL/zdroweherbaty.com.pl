<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nowy subskrybent newslettera</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #2c5e2e;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }

        .info-box {
            background-color: white;
            padding: 20px;
            border-left: 4px solid #2c5e2e;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }

        .unsubscribe-link {
            color: #666;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🍵 ZdroweHerbaty.com.pl</h1>
        <h2>Nowy subskrybent newslettera</h2>
    </div>

    <div class="content">
        <p>Witamy!</p>
        <p>Mamy nową osobę, która zapisała się na newsletter ZdroweHerbaty.com.pl</p>

        <div class="info-box">
            <h3>📧 Informacje o subskrybencie:</h3>
            <p><strong>Email:</strong> {{ $subscription->email }}</p>
            <p><strong>Data zapisu:</strong> {{ $subscription->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Adres IP:</strong> {{ $subscription->ip_address ?? 'Nieznany' }}</p>
        </div>

        <p>Subskrybent został pomyślnie dodany do bazy danych i będzie otrzymywać informacje o nowościach, promocjach i
            artykułach na blogu.</p>

        <p>Możesz zarządzać subskrypcjami w panelu administracyjnym.</p>
    </div>

    <div class="footer">
        <p>Ten email został wygenerowany automatycznie przez system newslettera ZdroweHerbaty.com.pl</p>
        <p>Jeśli otrzymałeś ten email przez pomyłkę, skontaktuj się z administratorem.</p>
    </div>
</body>

</html>
