# Чек-лист для решения ошибки 500 при оформлении заказа

## Быстрая диагностика

### 1. Проверка переменных окружения

Откройте файл `.env` и убедитесь, что присутствуют следующие переменные:

```env
TELEGRAM_BOT_TOKEN=ваш_токен_от_BotFather
TELEGRAM_BOT_USERNAME=ваш_username_бота_без_@
```

**Проверка через команду:**
```bash
php artisan tinker
```
В tinker выполните:
```php
config('verification.telegram.bot_token')
config('verification.telegram.bot_username')
```

Если значения `null` - переменные не установлены в `.env`.

**Решение:**
1. Добавьте переменные в `.env`
2. Выполните: `php artisan config:clear`
3. Проверьте снова

### 2. Проверка базы данных

Убедитесь, что все миграции выполнены:

```bash
php artisan migrate:status
```

Все миграции должны быть выполнены (Status: Ran).

**Если есть невыполненные миграции:**
```bash
php artisan migrate
```

### 3. Проверка структуры таблицы

Проверьте, что поля `code` и `telegram_chat_id` могут быть NULL:

```bash
php artisan tinker
```

В tinker выполните:
```php
\DB::select("PRAGMA table_info(phone_verifications)");
```

Или для MySQL:
```php
\DB::select("DESCRIBE phone_verifications");
```

Поля `code` и `telegram_chat_id` должны иметь `null` = YES.

### 4. Проверка логов

Проверьте логи Laravel для детальной информации об ошибке:

```bash
tail -n 100 storage/logs/laravel.log
```

Или откройте файл `storage/logs/laravel.log` и найдите последние ошибки.

### 5. Проверка конфигурации

Убедитесь, что файл `config/verification.php` существует и правильно настроен:

```bash
php artisan config:show verification
```

### 6. Проверка маршрутов

Убедитесь, что маршруты зарегистрированы:

```bash
php artisan route:list | grep verification
```

Должны быть видны:
- `POST /api/phone/verification/start`
- `POST /api/phone/verification/verify`
- `POST /api/telegram/webhook`

## Типичные ошибки и решения

### Ошибка: "Telegram бот не настроен"

**Причина:** В `.env` отсутствует `TELEGRAM_BOT_USERNAME`

**Решение:**
1. Добавьте в `.env`: `TELEGRAM_BOT_USERNAME=ваш_username_бота`
2. Выполните: `php artisan config:clear`

### Ошибка: "SQLSTATE[23000]: Integrity constraint violation"

**Причина:** Поля `code` или `telegram_chat_id` не могут быть NULL

**Решение:**
1. Выполните миграцию: `php artisan migrate`
2. Проверьте структуру таблицы

### Ошибка: "Call to undefined method"

**Причина:** Метод не существует в модели или сервисе

**Решение:**
1. Проверьте, что все файлы сохранены
2. Выполните: `composer dump-autoload`
3. Очистите кэш: `php artisan optimize:clear`

### Ошибка: "Class not found"

**Причина:** Класс не найден

**Решение:**
1. Проверьте namespace в файлах
2. Выполните: `composer dump-autoload`
3. Проверьте, что все файлы на месте

## Пошаговая проверка

### Шаг 1: Проверка .env

```bash
# Проверьте наличие переменных
grep TELEGRAM .env
```

Должны быть:
- `TELEGRAM_BOT_TOKEN=...`
- `TELEGRAM_BOT_USERNAME=...`

### Шаг 2: Очистка кэша

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Шаг 3: Проверка миграций

```bash
php artisan migrate:status
php artisan migrate
```

### Шаг 4: Проверка через tinker

```bash
php artisan tinker
```

Выполните:
```php
// Проверка конфигурации
config('verification.telegram.bot_token');
config('verification.telegram.bot_username');

// Проверка модели
\App\Models\PhoneVerification::first();

// Проверка создания записи
$order = \App\Models\Order::first();
\App\Services\PhoneVerificationService::class;
```

### Шаг 5: Тестирование API

Откройте браузер и проверьте Network вкладку (F12):
1. Попробуйте создать заказ
2. Посмотрите на запрос к `/api/phone/verification/start`
3. Проверьте ответ сервера

## Включение детальных ошибок

Для разработки включите показ детальных ошибок:

В файле `.env` установите:
```env
APP_DEBUG=true
```

**ВАЖНО:** В продакшене всегда должно быть `APP_DEBUG=false`

## Проверка через браузер

1. Откройте консоль разработчика (F12)
2. Перейдите на вкладку Network
3. Попробуйте создать заказ
4. Найдите запрос с ошибкой 500
5. Откройте его и посмотрите Response - там будет детальная информация об ошибке

## Контакты для помощи

Если проблема не решена:
1. Проверьте логи: `storage/logs/laravel.log`
2. Проверьте консоль браузера (F12)
3. Проверьте Network вкладку для HTTP ошибок
4. Убедитесь, что все переменные окружения установлены











