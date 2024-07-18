<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$token = '7054672801:AAG7CbhotKRuDDn9hALjTQ2C_HoPJjF_DrA';
$tgApi = "https://api.telegram.org/bot$token/";

$client = new Client(['base_uri' => $tgApi]);

$currency = new Currency();

$bot = new BotHandler($client);

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $miid =$message->message_id;
    $name = $message->from->first_name;
    $fromid = $message->from->id;
    $text = $message->text;
    $photo = $message->photo ?? '';
    $video = $message->video ?? '';
    $audio = $message->audio ?? '';
    $voice = $message->voice ?? '';
    $reply = $message->reply_markup ?? '';

    if ($text === '/start') {
        $bot->handleStartCommand($chat_id);
        return;
    }

    if (is_numeric($text)) {
        $bot->handleAmount($chat_id, (int)$text);
    } else {
        $bot->handleNonNumericInput($chat_id);
    }
}

if ($update->callback_query) {
    $callbackQuery = $update->callback_query;
    $callbackData = $callbackQuery->data;
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;

    $currency->storeState($chatId, $callbackData);

    $bot->http->post('sendMessage', [
        'form_params' => [
            'chat_id' => $chatId,
            'text' => 'Please, enter amount:'
        ]
    ]);
    return;
}

