<?php

namespace App\Services;

use GuzzleHttp\Client;

class DwollaService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api-sandbox.dwolla.com/',
            'headers' => [
                'Authorization' => 'Bearer '.env('DWOLLA_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function createCustomer($name, $email)
    {
        $response = $this->client->post('customers', [
            'json' => [
                'firstName' => $name,
                'lastName' => 'Demo',
                'email' => $email,
                'type' => 'business'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['_links']['self']['href'] ?? null;
    }
}
