<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Возврат на сайт</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-orange-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Возвращаем вас на сайт…</h2>
        
        @if($order)
            <p class="text-gray-600 mb-6">
                Заказ #{{ $order->id }} успешно подтвержден!
            </p>
        @endif
        
        <a id="openBrowser" href="{{ $redirectUrl }}" class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
            Открыть сайт
        </a>
        
        <div id="ios-instruction" class="hidden mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                Нажмите <strong>⋯</strong> → <strong>«Открыть в браузере»</strong> или <strong>«Открыть в Safari»</strong>
            </p>
        </div>
    </div>

    <script>
        const url = "{{ $redirectUrl }}";
        const ua = navigator.userAgent.toLowerCase();
        const isTelegram = ua.includes('telegram');
        const isAndroid = ua.includes('android');
        const isIOS = ua.includes('iphone') || ua.includes('ipad');

        if (isTelegram) {
            // Android - используем Intent для открытия в Chrome
            if (isAndroid) {
                // Убираем протокол из URL для Intent
                const urlWithoutProtocol = url.replace(/^https?:\/\//, "");
                // Используем Android Intent для открытия в Chrome
                location.href = "intent://" + urlWithoutProtocol + "#Intent;scheme=https;package=com.android.chrome;end";
                
                // Fallback - если Chrome не установлен, попробуем открыть в любом браузере
                setTimeout(function() {
                    location.href = url;
                }, 1000);
            }
            
            // iOS - показываем инструкцию
            if (isIOS) {
                document.getElementById('ios-instruction').classList.remove('hidden');
                // Показываем инструкцию и оставляем ссылку для ручного открытия
            }
        } else {
            // Если не Telegram, просто редиректим
            location.href = url;
        }
    </script>
</body>
</html>

