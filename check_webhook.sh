#!/bin/bash

# Скрипт для проверки вебхука Telegram бота
# Использование: ./check_webhook.sh ВАШ_ТОКЕН

if [ -z "$1" ]; then
    echo "Использование: $0 ВАШ_ТОКЕН_БОТА"
    exit 1
fi

TOKEN=$1

echo "=== Проверка вебхука Telegram бота ==="
echo ""

# Проверка информации о вебхуке
echo "1. Проверка текущего вебхука..."
WEBHOOK_INFO=$(curl -s "https://api.telegram.org/bot$TOKEN/getWebhookInfo")

if [ $? -eq 0 ]; then
    echo "Статус: OK"
    URL=$(echo $WEBHOOK_INFO | grep -o '"url":"[^"]*' | cut -d'"' -f4)
    PENDING=$(echo $WEBHOOK_INFO | grep -o '"pending_update_count":[0-9]*' | cut -d':' -f2)
    ERROR=$(echo $WEBHOOK_INFO | grep -o '"last_error_message":"[^"]*' | cut -d'"' -f4)
    
    if [ -z "$URL" ] || [ "$URL" = "null" ]; then
        echo "⚠️  ВЕБХУК НЕ НАСТРОЕН!" | grep --color=always ".*"
        echo ""
        echo "Для настройки вебхука выполните:"
        echo "curl -X POST \"https://api.telegram.org/bot$TOKEN/setWebhook?url=https://ВАШ_ДОМЕН/api/telegram/webhook\""
    else
        echo "URL вебхука: $URL"
        echo "Ожидающих обновлений: ${PENDING:-0}"
        if [ -n "$ERROR" ]; then
            echo "Последняя ошибка: $ERROR" | grep --color=always ".*"
        fi
    fi
else
    echo "Ошибка при проверке вебхука"
fi

echo ""
echo "=== Проверка информации о боте ==="
BOT_INFO=$(curl -s "https://api.telegram.org/bot$TOKEN/getMe")

if [ $? -eq 0 ]; then
    FIRST_NAME=$(echo $BOT_INFO | grep -o '"first_name":"[^"]*' | cut -d'"' -f4)
    USERNAME=$(echo $BOT_INFO | grep -o '"username":"[^"]*' | cut -d'"' -f4)
    BOT_ID=$(echo $BOT_INFO | grep -o '"id":[0-9]*' | cut -d':' -f2)
    
    if [ -n "$FIRST_NAME" ]; then
        echo "Имя бота: $FIRST_NAME"
        echo "Username: @$USERNAME"
        echo "ID бота: $BOT_ID"
    else
        echo "Ошибка: Неверный токен бота!" | grep --color=always ".*"
    fi
else
    echo "Ошибка при получении информации о боте"
fi











