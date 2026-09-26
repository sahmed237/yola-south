<?php

namespace App\Services\Payment\PaymentApi;

use Illuminate\Support\Facades\Http;
use Exception;

class PaystackApi
{
    private static $instance = null;
    private string $secretKey;
    private string $baseUrl;

    private function __construct()
    {
        $this->secretKey = \App\Models\Setting::where('key', 'paystack_secret_key')->value('value') ?: config('services.paystack.secret_key', '');
        $this->baseUrl = 'https://api.paystack.co';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function post(string $endpoint, array $body = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/' . ltrim($endpoint, '/'), $body);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function get(string $endpoint, array $query = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/' . ltrim($endpoint, '/'), $query);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function put(string $endpoint, array $body = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->put($this->baseUrl . '/' . ltrim($endpoint, '/'), $body);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function createSubAccount(array $data): array
    {
        return $this->post('/subaccount', [
            'business_name' => $data['business_name'],
            'settlement_bank' => $data['settlement_bank'],
            'account_number' => $data['account_number'],
            'percentage_charge' => $data['percentage_charge'] ?? 0,
            'description' => $data['description'] ?? '',
        ]);
    }

    public function updateSubAccount(string $subAccountCode, array $data): array
    {
        return $this->put('/subaccount/' . $subAccountCode, $data);
    }

    public function getBanks(): array
    {
        return $this->get('/bank')['data'] ?? [];
    }

    private function handleError($response)
    {
        $data = $response->json();
        $message = $data['message'] ?? 'Paystack API request failed';
        throw new Exception('Paystack API error: ' . $message);
    }
}
