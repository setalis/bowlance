# Бот не отвечает на команду /start - Диагностика

## Быстрая проверка

### Шаг 1: Проверка настройки вебхука

Выполните на сервере (замените `ВАШ_ТОКЕН_БОТА`):

```bash
curl "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/getWebhookInfo"
```

**Что проверить:**
- `url` - должен совпадать с вашим доменом
- `pending_update_count` - должно быть 0 или небольшое число
- `last_error_message` - должно быть пустым

**Если вебхук не настроен:**
```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/setWebhook?url=https://bowlance.dr-chenkova.com/api/telegram/webhook"
```

### Шаг 2: Проверка доступности URL

Проверьте, что URL доступен:

```bash
curl -I https://bowlance.dr-chenkova.com/api/telegram/webhook
```

Должен вернуться HTTP статус (200, 405, или 404 - все нормально).

### Шаг 3: Проверка логов

Проверьте логи Laravel на сервере:

```bash
tail -n 100 storage/logs/laravel.log | grep -i telegram
```

Или все последние записи:
```bash
tail -n 50 storage/logs/laravel.log
```

**Что искать:**
- Записи "Telegram webhook received" - значит запросы приходят
- Ошибки отправки сообщений
- Ошибки в коде webhook контроллера

### Шаг 4: Тестовая отправка запроса

Отправьте тестовый запрос на вебхук:

```bash
curl -X POST https://bowlance.dr-chenkova.com/api/telegram/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "chat": {
        "id": 123456789
      },
      "text": "/start"
    }
  }'
```

Должен вернуться: `{"ok":true}`

### Шаг 5: Проверка переменных окружения

```bash
php artisan tinker
```

В tinker:
```php
config('verification.telegram.bot_token');
config('verification.telegram.bot_username');
```

Оба значения должны быть заполнены.

## Пошаговая диагностика

### Проблема 1: Вебхук не настроен

**Симптомы:**
- `getWebhookInfo` возвращает пустой `url`
- Бот не отвечает вообще

**Решение:**
```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/setWebhook?url=https://bowlance.dr-chenkova.com/api/telegram/webhook"
```

### Проблема 2: Вебхук настроен неправильно

**Симптомы:**
- `getWebhookInfo` показывает другой URL
- `last_error_message` содержит ошибку

**Решение:**
1. Удалите старый вебхук:
```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/deleteWebhook"
```

2. Установите правильный:
```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/setWebhook?url=https://bowlance.dr-chenkova.com/api/telegram/webhook"
```

### Проблема 3: URL недоступен

**Симптомы:**
- `curl -I` возвращает ошибку
- `last_error_message` содержит "Connection refused" или "SSL error"

**Решение:**
1. Проверьте SSL сертификат:
```bash
openssl s_client -connect bowlance.dr-chenkova.com:443
```

2. Проверьте, что сайт доступен из интернета

3. Проверьте настройки веб-сервера (Nginx/Apache)

### Проблема 4: Запросы приходят, но бот не отвечает

**Симптомы:**
- В логах есть "Telegram webhook received"
- Но бот не отправляет сообщения

**Решение:**
1. Проверьте токен бота в `.env`
2. Проверьте логи на ошибки отправки
3. Проверьте, что токен правильный:
```bash
curl "https://api.telegram.org/botВАШ_ТОКЕН/getMe"
```

Должен вернуться информация о боте.

## Проверка через Telegram API

### Проверка информации о боте

```bash
curl "https://api.telegram.org/botВАШ_ТОКЕН/getMe"
```

Должен вернуться JSON с информацией о боте.

### Проверка последних обновлений (если вебхук не работает)

```bash
curl "https://api.telegram.org/botВАШ_ТОКЕН/getUpdates"
```

Это покажет последние сообщения боту (если вебхук не настроен).

## Мониторинг в реальном времени

### Просмотр логов в реальном времени

На сервере:
```bash
tail -f storage/logs/laravel.log
```

Затем отправьте `/start` боту - должны появиться записи в логах.

### Проверка статуса вебхука

```bash
watch -n 5 'curl -s "https://api.telegram.org/botВАШ_ТОКЕН/getWebhookInfo" | jq'
```

## Типичные ошибки

### Ошибка: "SSL certificate error"

**Решение:**
- Установите валидный SSL сертификат
- Проверьте конфигурацию веб-сервера

### Ошибка: "Connection refused"

**Решение:**
- Проверьте, что веб-сервер запущен
- Проверьте firewall настройки
- Проверьте, что порт 443 открыт

### Ошибка: "Invalid token"

**Решение:**
- Проверьте токен бота в `.env`
- Убедитесь, что токен правильный (без пробелов, лишних символов)
- Выполните `php artisan config:clear`

### Ошибка: "Webhook URL must use HTTPS"

**Решение:**
- Убедитесь, что URL начинается с `https://`
- Проверьте SSL сертификат

## Безопасность Webhook

### Почему CSRF не нужен для Telegram webhook?

CSRF защита не нужна для Telegram webhook, потому что:
1. **Telegram не может предоставить CSRF токен** - это внешний сервис
2. **Используются другие методы защиты** (см. ниже)

### Методы защиты webhook

1. **Секретный токен (Secret Token)** - РЕКОМЕНДУЕТСЯ
   - Telegram отправляет заголовок `X-Telegram-Bot-Api-Secret-Token`
   - Настройте в `.env`: `TELEGRAM_WEBHOOK_SECRET_TOKEN=ваш_секретный_токен`
   - При настройке webhook укажите: `?secret_token=ваш_секретный_токен`

2. **Проверка IP адресов Telegram**
   - Код автоматически проверяет IP адреса Telegram
   - Логирует подозрительные запросы

3. **HTTPS обязателен**
   - Telegram требует HTTPS для webhook
   - Все данные передаются в зашифрованном виде

4. **Валидация данных**
   - Проверка структуры запроса
   - Логирование всех запросов

### Настройка Secret Token

1. Сгенерируйте случайный токен:
```bash
php artisan tinker
# В tinker:
\Illuminate\Support\Str::random(32)
# Или:
use Illuminate\Support\Str;
Str::random(32)
```

2. Добавьте в `.env`:
```env
TELEGRAM_WEBHOOK_SECRET_TOKEN=ваш_сгенерированный_токен
```

3. Настройте webhook с токеном:
```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook?url=https://bowlance.dr-chenkova.com/api/telegram/webhook&secret_token=ваш_сгенерированный_токен"
```

## Чек-лист

- [ ] Вебхук настроен через `setWebhook`
- [ ] URL вебхука совпадает с вашим доменом
- [ ] URL доступен из интернета
- [ ] SSL сертификат валиден
- [ ] Переменные окружения установлены
- [ ] Токен бота правильный
- [ ] Secret Token настроен (рекомендуется)
- [ ] Маршрут `/api/telegram/webhook` зарегистрирован
- [ ] Логи Laravel не показывают ошибок
- [ ] Тестовый запрос возвращает `{"ok":true}`

## Быстрое решение

Если ничего не помогает, выполните на сервере:

```bash
# 1. Проверьте токен
php artisan tinker
# В tinker: config('verification.telegram.bot_token')

# 2. Очистите кэш
php artisan config:clear
php artisan cache:clear

# 3. Удалите и переустановите вебхук
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/deleteWebhook"
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook?url=https://bowlance.dr-chenkova.com/api/telegram/webhook"

# 4. Проверьте статус
curl "https://api.telegram.org/botВАШ_ТОКЕН/getWebhookInfo"

# 5. Проверьте логи
tail -f storage/logs/laravel.log
```

Затем отправьте `/start` боту и смотрите логи.

