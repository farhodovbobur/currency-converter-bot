<?php

declare(strict_types=1);

use GuzzleHttp\Client;

class Currency
{
    private Client $http;
    private PDO $pdo;

    public function __construct()
    {
        $this->http = new Client(['base_uri'=>'https://cbu.uz/oz/arkhiv-kursov-valyut/json/']);
        $this->pdo  = DB::connect();
    }

    public function getRates()
    {
        return json_decode($this->http->get('')->getBody()->getContents());
    }

    public function getUsd()
    {
        return $this->getRates()[0];
    }

    public function convert(
        int    $chatId,
        string $originalCurrency,
        string $targetCurrency,
        float  $amount
    ) {
        $now    = date('Y-m-d H:i:s');
        $status = "{$originalCurrency}2{$targetCurrency}";
        $rate   = $this->getUsd()->Rate;

        $stmt = $this->pdo->prepare("INSERT INTO users (chat_id, amount, status, created_at) VALUES (:chatId, :amount, :status, :createdAt)");
        $stmt->bindParam(':chatId', $chatId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':createdAt', $now);
        $stmt->execute();

        if ($originalCurrency === 'usd') {
            $result = $amount * $rate;
        } else {
            $result = $amount / $rate;
        }

        $result = number_format($result, 2, '.', ' ');
        return $result . " " . $targetCurrency;
    }

    public function storeState(int $chatId, string $state): void
    {
        $query = "INSERT INTO currency_bot (chat_id, conversion_type, created_at) VALUES (:chatId, :conversionType, :createdAt)";
        $now   = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':chatId', $chatId);
        $stmt->bindParam(':conversionType', $state);
        $stmt->bindParam(':createdAt', $now);
        $stmt->execute();
    }

    public function calculateForBot(int $chatId, int|float $amount): string
    {
        $query = "SELECT conversion_type FROM currency_bot WHERE currency_bot.chat_id = :chatId ORDER BY created_at DESC LIMIT 1";
        $stmt  = $this->pdo->prepare($query);
        $stmt->bindParam(':chatId', $chatId);
        $stmt->execute();

        $conversionType = $stmt->fetchObject()->conversion_type;

        [$originalCurrency, $targetCurrency] = explode('2', $conversionType);

        return $this->convert((int)$chatId, $originalCurrency, $targetCurrency, $amount);
    }

}