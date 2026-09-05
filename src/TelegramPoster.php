<?php

class TelegramPoster {
    private string $token;
    private string $chatId;

    public function __construct($token, $chatId) {
        $this->token = $token;
        $this->chatId = $chatId;
    }

    public function post(string $text): void {
        file_get_contents("https://api.telegram.org/bot{$this->token}/sendMessage?" . http_build_query([
            'chat_id' => '-100' . $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]));
    }
}
