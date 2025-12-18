# Настройка вебхука для локального сайта

## Проблема: Бот не отвечает на команду /start

Если ваш сайт работает локально (localhost), Telegram не может отправить вебхук на ваш компьютер напрямую. Нужно использовать туннель (например, ngrok) для доступа из интернета.

## Решение: Использование ngrok

### Шаг 1: Установка ngrok

1. Перейдите на https://ngrok.com/
2. Зарегистрируйтесь (бесплатно)
3. Скачайте ngrok для Windows
4. Распакуйте файл `ngrok.exe` в удобную папку (например, `C:\ngrok\`)

### Шаг 2: Получение токена авторизации

1. После регистрации на сайте ngrok вы получите токен авторизации
2. Выполните команду (замените `ВАШ_ТОКЕН` на реальный токен):

```powershell
C:\ngrok\ngrok.exe config add-authtoken ВАШ_ТОКЕН
```

Или если ngrok в PATH:
```powershell
ngrok config add-authtoken ВАШ_ТОКЕН
```

### Шаг 3: Запуск локального сервера Laravel

В первом терминале запустите Laravel:

```bash
php artisan serve
```

Сервер запустится на `http://localhost:8000`

### Шаг 4: Запуск ngrok туннеля

Во втором терминале запустите ngrok:

```powershell
C:\ngrok\ngrok.exe http 8000
```

Или если ngrok в PATH:
```powershell
ngrok http 8000
```

Вы увидите что-то вроде:
```
Forwarding   https://abc123.ngrok-free.app -> http://localhost:8000
```

**Скопируйте HTTPS URL** (например: `https://abc123.ngrok-free.app`)

### Шаг 5: Настройка вебхука

Выполните команду (замените значения):

```powershell
$token = "ВАШ_ТОКЕН_БОТА"
$ngrokUrl = "https://abc123.ngrok-free.app"
Invoke-WebRequest -Uri "https://api.telegram.org/bot$token/setWebhook?url=$ngrokUrl/api/telegram/webhook" -Method POST
```

Или через curl:
```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/setWebhook?url=https://abc123.ngrok-free.app/api/telegram/webhook"
```

### Шаг 6: Проверка вебхука

Проверьте, что вебхук настроен правильно:

```powershell
$token = "ВАШ_ТОКЕН_БОТА"
Invoke-WebRequest -Uri "https://api.telegram.org/bot$token/getWebhookInfo"
```

Или через curl:
```bash
curl "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/getWebhookInfo"
```

Должен вернуться JSON с вашим ngrok URL.

### Шаг 7: Тестирование

1. Убедитесь, что оба терминала работают:
   - Laravel сервер запущен
   - ngrok туннель активен
2. Откройте Telegram
3. Найдите вашего бота
4. Отправьте `/start`
5. Бот должен ответить

## Важные замечания

### ngrok URL меняется

**Проблема:** На бесплатном плане ngrok URL меняется при каждом запуске.

**Решение:**
1. Используйте статический домен (платная функция ngrok)
2. Или настраивайте вебхук заново при каждом запуске ngrok

### Проверка логов

Если бот не отвечает, проверьте логи:

```bash
tail -f storage/logs/laravel.log
```

Ищите записи с "Telegram webhook" для отладки.

### Альтернативные решения

#### Вариант 1: Использование локтunnel

```bash
npx localtunnel --port 8000
```

#### Вариант 1: Использование serveo

```bash
ssh -R 80:localhost:8000 serveo.net
```

## Проверка работы вебхука

### Тест 1: Проверка доступности URL

Откройте в браузере:
```
https://ваш_ngrok_url/api/telegram/webhook
```

Должен вернуться JSON ответ (даже если это ошибка 405 - это нормально).

### Тест 2: Отправка тестового запроса

```powershell
$body = @{
    message = @{
        chat = @{
            id = 123456789
        }
        text = "/start"
    }
} | ConvertTo-Json -Depth 10

Invoke-WebRequest -Uri "https://ваш_ngrok_url/api/telegram/webhook" -Method POST -Body $body -ContentType "application/json"
```

### Тест 3: Проверка через Telegram

1. Откройте Telegram
2. Найдите вашего бота
3. Отправьте `/start`
4. Проверьте логи Laravel - должны появиться записи

## Решение проблем

### Проблема: "Webhook was set" но бот не отвечает

**Причины:**
1. ngrok туннель не активен
2. Laravel сервер не запущен
3. Ошибка в коде webhook контроллера

**Решение:**
1. Проверьте, что оба процесса запущены
2. Проверьте логи Laravel
3. Проверьте консоль ngrok на ошибки

### Проблема: "SSL certificate error"

**Причина:** ngrok использует самоподписанный сертификат

**Решение:** Это нормально для ngrok, Telegram принимает такие сертификаты

### Проблема: "Connection refused"

**Причина:** Laravel сервер не запущен или недоступен

**Решение:**
1. Запустите `php artisan serve`
2. Проверьте, что порт 8000 свободен
3. Убедитесь, что ngrok указывает на правильный порт

## Для продакшена

На продакшене используйте реальный домен с HTTPS:

```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook?url=https://ваш_домен.com/api/telegram/webhook"
```

Убедитесь, что:
- Домен имеет валидный SSL сертификат
- URL доступен из интернета
- Маршрут `/api/telegram/webhook` работает




