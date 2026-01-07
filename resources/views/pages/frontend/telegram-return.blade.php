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
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-800 font-semibold mb-1">
                    ✅ Заказ #{{ $order->id }} успешно подтвержден!
                </p>
                <p class="text-sm text-green-700">
                    Статус: {{ $order->status === 'new' ? 'Принят в обработку' : $order->status }}
                </p>
            </div>
        @else
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800 text-sm">
                    ⚠️ Заказ не найден. Проверьте статус в личном кабинете.
                </p>
            </div>
        @endif
        
        <a id="openBrowser" href="{{ $redirectUrl }}" target="_blank" class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors mb-4">
            Открыть сайт
        </a>
        
        <!-- Блок для отображения отладочной информации -->
        <div id="debug-info" class="hidden mt-4 p-3 bg-gray-100 rounded text-left text-xs text-gray-600">
            <p><strong>Отладочная информация:</strong></p>
            <p id="debug-text"></p>
        </div>
        
        <div id="android-instruction" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800 mb-2">
                <strong>Для Android:</strong>
            </p>
            <p class="text-sm text-yellow-800">
                Нажмите на кнопку выше, затем выберите <strong>"Открыть в браузере"</strong> или <strong>"Открыть в Chrome"</strong>
            </p>
        </div>
        
        <div id="ios-instruction" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800 mb-2">
                <strong>Для iOS:</strong>
            </p>
            <p class="text-sm text-blue-800">
                Нажмите <strong>⋯</strong> (три точки) → <strong>«Открыть в браузере»</strong> или <strong>«Открыть в Safari»</strong>
            </p>
        </div>
    </div>

    <script>
        const url = "{{ $redirectUrl }}";
        const ua = navigator.userAgent.toLowerCase();
        const isTelegram = ua.includes('telegram');
        const isAndroid = ua.includes('android');
        const isIOS = ua.includes('iphone') || ua.includes('ipad');

        // Отображаем отладочную информацию на странице
        const debugInfo = document.getElementById('debug-info');
        const debugText = document.getElementById('debug-text');
        
        function showDebugInfo(text) {
            if (debugInfo && debugText) {
                debugInfo.classList.remove('hidden');
                debugText.innerHTML += '<p>' + text + '</p>';
            }
            console.log(text);
        }
        
        showDebugInfo('User Agent: ' + ua);
        showDebugInfo('Is Telegram: ' + isTelegram);
        showDebugInfo('Is Android: ' + isAndroid);
        showDebugInfo('Is iOS: ' + isIOS);
        showDebugInfo('Redirect URL: ' + url);

        if (isTelegram) {
            // Android - пытаемся использовать Intent, но всегда показываем инструкцию
            if (isAndroid) {
                showDebugInfo('Обнаружено Android устройство в Telegram');
                document.getElementById('android-instruction').classList.remove('hidden');
                
                // Пытаемся открыть через Intent (может не сработать в Telegram WebView)
                try {
                    const urlWithoutProtocol = url.replace(/^https?:\/\//, "");
                    const intentUrl = "intent://" + urlWithoutProtocol + "#Intent;scheme=https;package=com.android.chrome;end";
                    showDebugInfo('Попытка открыть через Intent: ' + intentUrl);
                    
                    // Создаем скрытую ссылку с Intent
                    const intentLink = document.createElement('a');
                    intentLink.href = intentUrl;
                    intentLink.style.display = 'none';
                    document.body.appendChild(intentLink);
                    intentLink.click();
                    
                    showDebugInfo('Intent ссылка создана и активирована');
                    
                    // Удаляем ссылку
                    setTimeout(() => {
                        if (document.body.contains(intentLink)) {
                            document.body.removeChild(intentLink);
                        }
                    }, 100);
                } catch (e) {
                    showDebugInfo('Ошибка при попытке открыть через Intent: ' + e.message);
                }
                
                // Всегда показываем кнопку и инструкцию
                document.getElementById('openBrowser').style.display = 'inline-block';
                showDebugInfo('Показана кнопка и инструкция для Android');
            } else if (isIOS) {
                // iOS - показываем инструкцию
                showDebugInfo('Обнаружено iOS устройство в Telegram');
                document.getElementById('ios-instruction').classList.remove('hidden');
                document.getElementById('openBrowser').style.display = 'inline-block';
                showDebugInfo('Показана инструкция для iOS');
            } else {
                // Другие платформы - показываем ссылку
                showDebugInfo('Обнаружена другая платформа в Telegram');
                document.getElementById('openBrowser').style.display = 'inline-block';
            }
        } else {
            // Если не Telegram, просто редиректим
            showDebugInfo('Не Telegram, автоматический редирект на: ' + url);
            window.location.href = url;
        }
    </script>
</body>
</html>

