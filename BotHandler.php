<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BotHandler
{
    public Client $http;
    public array $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🇺🇸USD  >  🇺🇿UZS', 'callback_data' => 'usd2uzs'],
                ['text' => '🇺🇿UZS  >  🇺🇸USD', 'callback_data' => 'uzs2usd']
            ]
        ]
    ];

    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    public function handleStartCommand(int $chatId): void
    {
        $this->http->post('sendMessage', [
            'form_params' => [
                'chat_id'      => $chatId,
                'text'         => 'Welcome to Currency Converter Bot. Please chose conversion type:',
                'reply_markup' => json_encode($this->keyboard)
            ]
        ]);
    }

    public function handleAmount(int $chatId, int $amount): void
    {
        $currency     = new Currency();
        $calculations = $currency->calculateForBot($chatId, $amount);
        $responseText = "The result: $calculations \n\nFor other conversion operations, use the buttons below";
        $this->http->post('sendMessage', [
            'form_params' => [
                'chat_id'      => $chatId,
                'text'         => $responseText,
                'reply_markup' => json_encode($this->keyboard)
            ]
        ]);
    }

    public function handleNonNumericInput(int $chatId): void
    {
        $this->http->post('sendMessage', [
            'form_params' => [
                'chat_id' => $chatId,
                'text'    => "Please, enter a valid amount. Try numeric value:",
            ]
        ]);
    }
}

