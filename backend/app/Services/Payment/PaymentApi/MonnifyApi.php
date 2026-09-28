<?php

namespace App\Services\Payment\PaymentApi;

use Illuminate\Support\Facades\Http;
use Exception;

class MonnifyApi
{
    private static $instance = null;
    private $apiKey;
    private $secretKey;
    private $baseUrl;
    private $token = null;

    private function __construct()
    {
        $this->apiKey = \App\Models\Setting::where('key', 'monnify_api_key')->value('value') ?: config('services.monnify.api_key', '');
        $this->secretKey = \App\Models\Setting::where('key', 'monnify_secret_key')->value('value') ?: config('services.monnify.secret_key', '');
        $isLive = filter_var(\App\Models\Setting::where('key', 'monnify_mode_live')->value('value'), FILTER_VALIDATE_BOOLEAN);
        $this->baseUrl = $isLive ? 'https://api.monnify.com' : 'https://sandbox.monnify.com';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function httpClient()
    {
        $verify = !app()->isLocal() && !app()->runningUnitTests() && !empty(ini_get('curl.cainfo'));
        return $verify ? Http::asJson() : Http::withoutVerifying()->asJson();
    }

    private function login(): string
    {
        if ($this->token) {
            return $this->token;
        }

        if (empty($this->apiKey) || empty($this->secretKey)) {
            throw new Exception('Monnify API Key or Secret Key is missing.');
        }

        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->secretKey),
        ])->post($this->baseUrl . '/api/v1/auth/login');

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['requestSuccessful']) && $data['requestSuccessful'] === true) {
                $this->token = $data['responseBody']['accessToken'];
                return $this->token;
            }
        }

        throw new Exception('Monnify login failed: ' . ($response->json('responseMessage') ?? 'Unknown error'));
    }

    public function post(string $endpoint, array $body = []): array
    {
        $token = $this->login();
        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/' . ltrim($endpoint, '/'), $body);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function get(string $endpoint, array $query = []): array
    {
        $token = $this->login();
        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/' . ltrim($endpoint, '/'), $query);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function createSubAccount(array $data): array
    {
        $response = $this->post('/api/v1/sub-accounts', [
            [
                'currencyCode' => 'NGN',
                'bankCode' => $data['bankCode'],
                'accountNumber' => $data['accountNumber'],
                'email' => $data['email'],
                'defaultSplitPercentage' => $data['defaultSplitPercentage'] ?? 0,
            ]
        ]);

        $responseBody = $response['responseBody'] ?? [];
        return $responseBody[0] ?? $response;
    }

    public function getBanks(): array
    {
        $response = $this->get('/api/v1/banks');
        return $response['responseBody'] ?? [];
    }

    public function delete(string $endpoint, array $query = []): array
    {
        $token = $this->login();
        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->delete($this->baseUrl . '/' . ltrim($endpoint, '/'), $query);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function deleteSubAccount(string $subAccountCode): array
    {
        return $this->delete('/api/v1/sub-accounts/' . $subAccountCode);
    }

    private function handleError($response)
    {
        $data = $response->json();
        $message = $data['responseMessage'] ?? ($data['error_description'] ?? 'Monnify API request failed');
        throw new Exception('Monnify API error: ' . $message);
    }
}
