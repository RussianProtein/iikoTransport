<?php
/**
 * @author Sergey Borguronov<s@htmlup.ru>
 * 10.08.21
 */
return [
    /**
     * В ENV конфиг нужно вписать параметр IIKO_API_LOGIN и разместить ключик от iikoTransport
     */

    'apiLogin' => env('IIKO_API_LOGIN', 'login'),

    /**
     * Включить логгирование?
     */
    'debug' => false,

    'channels' => [
        'telegram' => [
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'bot_key' => env('TELEGRAM_BOT_KEY')
        ]
    ]
];
