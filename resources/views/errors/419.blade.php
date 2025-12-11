@if (config('app.env') === 'production')
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <title>Refreshing...</title>
    </head>

    <body>
        <script>
            // Ciche odświeżenie aktualnej strony (tylko na produkcji)
            window.location.reload();
        </script>
    </body>

    </html>
@else
    @extends('layouts.app')

    @section('content')
        <div
            style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
            <div
                style="text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🕐</div>
                <h2 style="color: #333; margin-bottom: 15px;">Sesja wygasła (Debug)</h2>
                <p style="color: #666; margin-bottom: 25px; line-height: 1.6;">
                    Z powodu dłuższej nieaktywności Twoja sesja wygasła ze względów bezpieczeństwa.<br>
                    Na produkcji strona zostałaby cicho przekierowana.
                </p>
                <button onclick="window.location.reload()"
                    style="background-color: #026941; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                    Odśwież stronę
                </button>
            </div>
        </div>
    @endsection
@endif
