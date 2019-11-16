<?php

class Telegram
{
    const API_URL = 'https://api.telegram.org/bot';
    const API_KEY = TELEGRAM_API_KEY;

    public function sendRequest($method) {
        return get_url_content($this::API_URL.$this::API_KEY.'/'.$method);
    }

    public function clearUpdates() {
        $this->sendRequest('getUpdates?offset=-1');
    }

    public function getUpdates($array = true) {
        $json = $this->sendRequest('getUpdates');
        return (($array) ? json_decode($json, true) : $json);
    }

    public function sendMessage($text, $chat_id) {
        if(strlen(trim($text)) == 0) $text = "Error";
        return $this->sendRequest('sendMessage?chat_id='.$chat_id.'&text='.urldecode($text));
    }
}
?>
