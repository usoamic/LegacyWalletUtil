<?php

trait TelegramNotifier {
    private function sendNotification($message) {
        if(ENABLED_TELEGRAM) {
            $telegramClass = new Telegram();
            $telegramClass->sendMessage($message, ADMIN_TELEGRAM_CHAT_ID);
        }
    }
}