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

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
        }

        .logo {
            height: 80px;
            margin-top: 15px;
            margin-bottom: 10px;
            max-height: 80px;
            width: 160px;
            display: inline-block;
        }

        .header-title {
            margin: 0;
            font-size: 19px;
            font-weight: bold;
            margin-left: 20px;
            color: #ffffff;
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
            <div class="header-content">
                <div class="logo">
                    <flux:image.logo variant="standard" size="md" />
                </div>
                <h1 class="header-title">Nowa wiadomość z formularza kontaktowego</h1>
            </div>
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
