<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz kontaktowy</title>
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
            background-color: #026941;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            margin-bottom: 20px;
        }

        .field {
            margin-bottom: 15px;
        }

        .field-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .field-value {
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #026941;
        }

        .message {
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 3px solid #026941;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>Nowa wiadomość z formularza kontaktowego</h1>
        </div>

        <div class="content">
            <div class="field">
                <div class="field-label">Od:</div>
                <div class="field-value">{{ $name }} ({{ $email }})</div>
            </div>

            <div class="field">
                <div class="field-label">Wiadomość:</div>
                <div class="message">{{ $messageText }}</div>
            </div>
        </div>
    </div>
</body>

</html>

