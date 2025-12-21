# Проверка вебхука на сервере

## Быстрая проверка

### 1. Проверка настройки вебхука

Выполните команду (замените `ВАШ_ТОКЕН_БОТА` на реальный токен):

```bash
curl "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/getWebhookInfo"
```

**Ожидаемый ответ при правильной настройке:**
```json
{
  "ok": true,
  "result": {
    "url": "https://ваш_домен.com/api/telegram/webhook",
    "has_custom_certificate": false,
    "pending_update_count": 0,
    "last_error_date": 0,
    "last_error_message": "",
    "max_connections": 40
  }
}
```

**Что проверить:**
- `url` должен совпадать с вашим доменом
- `pending_update_count` должно быть 0 (или небольшое число)
- `last_error_message` должно быть пустым

### 2. Проверка доступности URL вебхука

Проверьте, что URL доступен:

```bash
curl -I https://ваш_домен.com/api/telegram/webhook
```

**Ожидаемый ответ:**
- HTTP статус: 200, 405, или 404 (все нормально, главное что сайт доступен)
- Не должно быть ошибок SSL

### 3. Проверка SSL сертификата

```bash
openssl s_client -connect ваш_домен.com:443 -servername ваш_домен.com
```

**Что проверить:**
- Сертификат должен быть валидным
- Не должно быть ошибок "certificate verify failed"

### 4. Тестовая отправка запроса на вебхук

Создайте тестовый запрос:

```bash
curl -X POST https://ваш_домен.com/api/telegram/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "chat": {
        "id": 123456789
      },
      "text": "/start test123"
    }
  }'
```

**Ожидаемый ответ:**
```json
{"ok":true}
```

### 5. Проверка логов Laravel на сервере

```bash
tail -n 100 storage/logs/laravel.log | grep -i telegram
```

Или посмотрите последние записи:
```bash
tail -n 50 storage/logs/laravel.log
```

**Что искать:**
- Записи "Telegram webhook received"
- Ошибки, связанные с Telegram
- Ошибки отправки сообщений

## Детальная проверка

### Шаг 1: Проверка переменных окружения

```bash
php artisan tinker
```

В tinker выполните:
```php
config('verification.telegram.bot_token');
config('verification.telegram.bot_username');
```

Оба значения должны быть заполнены.

### Шаг 2: Проверка маршрутов

```bash
php artisan route:list | grep telegram
```

Должен быть виден маршрут:
```
POST  api/telegram/webhook  api.telegram.webhook
```

### Шаг 3: Проверка прав доступа

Убедитесь, что веб-сервер может записывать в логи:

```bash
ls -la storage/logs/
```

Файл `laravel.log` должен существовать и быть доступен для записи.

### Шаг 4: Проверка конфигурации веб-сервера

#### Для Nginx

Проверьте конфигурацию:
```bash
nginx -t
```

Убедитесь, что настроен правильный `server_name` и SSL сертификат.

#### Для Apache

Проверьте конфигурацию:
```bash
apache2ctl configtest
```

### Шаг 5: Проверка через браузер

Откройте в браузере:
```
https://ваш_домен.com/api/telegram/webhook
```

**Ожидаемый результат:**
- Метод GET не поддерживается (405) - это нормально
- Или JSON ответ

## Проверка через Telegram

### Тест 1: Отправка команды /start

1. Откройте Telegram
2. Найдите вашего бота
3. Отправьте `/start`
4. Бот должен ответить

### Тест 2: Отправка команды /start с токеном

1. Создайте тестовый заказ на сайте
2. Скопируйте токен из URL бота
3. Отправьте `/start токен` в Telegram
4. Бот должен отправить код подтверждения

## Мониторинг в реальном времени

### Просмотр логов в реальном времени

```bash
tail -f storage/logs/laravel.log
```

Затем отправьте команду боту - в логах должны появиться записи.

### Проверка статуса вебхука

```bash
watch -n 5 'curl -s "https://api.telegram.org/botВАШ_ТОКЕН/getWebhookInfo" | jq'
```

Это будет проверять статус каждые 5 секунд.

## Автоматическая проверка

Создайте скрипт для проверки:

```bash
#!/bin/bash
# check_webhook.sh

TOKEN="ВАШ_ТОКЕН_БОТА"
DOMAIN="ваш_домен.com"

echo "Проверка вебхука..."
echo ""

# Проверка настройки вебхука
echo "1. Статус вебхука:"
curl -s "https://api.telegram.org/bot$TOKEN/getWebhookInfo" | jq

echo ""
echo "2. Доступность URL:"
curl -I "https://$DOMAIN/api/telegram/webhook" 2>&1 | head -1

echo ""
echo "3. Последние записи в логе:"
tail -n 5 storage/logs/laravel.log | grep -i telegram
```

Сохраните как `check_webhook.sh`, сделайте исполняемым:
```bash
chmod +x check_webhook.sh
./check_webhook.sh
```

## Решение проблем на сервере

### Проблема: Вебхук не работает

**Проверьте:**
1. SSL сертификат валиден
2. URL доступен из интернета
3. Маршрут зарегистрирован
4. Переменные окружения установлены

**Команды для проверки:**
```bash
# Проверка SSL
openssl s_client -connect ваш_домен.com:443

# Проверка доступности
curl -I https://ваш_домен.com/api/telegram/webhook

# Проверка переменных
php artisan tinker
# В tinker: config('verification.telegram.bot_token')
```

### Проблема: "SSL certificate error"

**Решение:**
1. Установите валидный SSL сертификат (Let's Encrypt, Cloudflare)
2. Проверьте конфигурацию веб-сервера
3. Убедитесь, что сертификат не истек

### Проблема: "Connection refused"

**Решение:**
1. Проверьте, что веб-сервер запущен
2. Проверьте firewall настройки
3. Убедитесь, что порт 443 открыт

### Проблема: Бот не отвечает, но вебхук настроен

**Решение:**
1. Проверьте логи Laravel на ошибки
2. Проверьте, что токен бота правильный
3. Проверьте, что код webhook контроллера работает
4. Отправьте тестовый запрос вручную

## Полезные команды

### Переустановка вебхука

```bash
# Удалить вебхук
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/deleteWebhook"

# Установить вебхук заново
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook?url=https://ваш_домен.com/api/telegram/webhook"
```

### Очистка ожидающих обновлений

Если `pending_update_count` большое число:

```bash
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/deleteWebhook?drop_pending_updates=true"
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook?url=https://ваш_домен.com/api/telegram/webhook"
```

### Просмотр последних ошибок вебхука

```bash
curl -s "https://api.telegram.org/botВАШ_ТОКЕН/getWebhookInfo" | jq '.result | {last_error_date, last_error_message}'
```

## Чек-лист проверки

- [ ] Вебхук настроен через `getWebhookInfo`
- [ ] URL вебхука совпадает с вашим доменом
- [ ] SSL сертификат валиден
- [ ] URL доступен из интернета
- [ ] Переменные окружения установлены
- [ ] Маршрут зарегистрирован
- [ ] Логи Laravel не показывают ошибок
- [ ] Бот отвечает на команду `/start`
- [ ] Код отправляется через бота

## Контакты для помощи

Если проблема не решена:
1. Проверьте логи: `storage/logs/laravel.log`
2. Проверьте статус вебхука через API
3. Проверьте конфигурацию веб-сервера
4. Убедитесь, что все переменные окружения установлены






