<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;
use App\Services\Payment\PaymentApi\ZainpayApi;
use Illuminate\Support\Facades\Log;
use Exception;

class ZainpayGateway implements PaymentGatewayInterface
{
    public function initialize($invoice, string $email, string $phone, string $callbackUrl) : PaymentInitResult
    {
        $invoiceId = $invoice->id ?? $invoice['id'];
        $amount = (float)($invoice->payment_amount ?? $invoice['payment_amount'] ?? 0);
        $zainboxCode = \App\Models\Setting::where('key', 'zainpayZainboxCode')->value('value') ?: config('services.zainpay.zainbox_code');
        $prefix = $invoice->prefix ?? $invoice['prefix'] ?? 'INV';
        $reference = $prefix . '-' . $invoiceId . '-' . time();

        try {
            $api = ZainpayApi::getInstance();
            $response = $api->post('zainbox/card/initialize/payment', [
                'amount' => (string)$amount,
                'txnRef' => $reference,
                'mobileNumber' => $phone,
                'emailAddress' => $email,
                'zainboxCode' => $zainboxCode,
                'callBackUrl' => $callbackUrl
            ]);

            if (isset($response['code']) && $response['code'] === '00') {
                return new PaymentInitResult(
                    reference: $reference,
                    redirectUrl: $response['data'],
                    rawResponse: json_encode($response)
                );
            }

            throw new Exception('Zainpay initialization failed: ' . ($response['description'] ?? 'Unknown error'));

        } catch (\Throwable $e) {
            throw new Exception('Zainpay initialization failed: ' . $e->getMessage());
        }
    }

    public function verify(string $reference) : PaymentVerifyResult
    {
        if (empty($reference)) {
            throw new Exception('Transaction reference is required.');
        }

        try {
            $api = ZainpayApi::getInstance();
            
            // Updated to the suggested V2 verification endpoint
            $response = $api->get('virtual-account/wallet/deposit/verify/v2/' . $reference);

            if (isset($response['code']) && $response['code'] === '00') {
                $data = $response['data'];
                
                return new PaymentVerifyResult(
                    amountPaid: (float) ($data['amount'] ?? $data['depositedAmount'] ?? 0),
                    paymentDate: $data['paymentDate'] ?? date('Y-m-d H:i:s'),
                    gateway: 'zainpay',
                    rawResponse: json_encode($data),
                    reference: $reference
                );
            }

            throw new Exception($response['description'] ?? 'Transaction not found');

        } catch (\Throwable $e) {
            Log::error('Zainpay verification error: ' . $e->getMessage());
            throw new Exception('Unable to verify Zainpay transaction: ' . $e->getMessage());
        }
    }

    public function webhook(array $payload, array $headers, string $rawBody) : ?PaymentVerifyResult
    {
        $signature = $headers['Zainpay-Signature'][0] ?? $headers['zainpay-signature'][0] ?? null;
        $secretKey = config('services.zainpay.public_key');

        if (!$signature || !$secretKey) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $secretKey);
        
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $data = $payload['data'] ?? [];
        $reference = $data['paymentRef'] ?? $payload['paymentRef'] ?? $payload['reference'] ?? null;

        if (!$reference) {
            return null;
        }

        try {
            return $this->verify($reference);
        } catch (\Exception $e) {
            Log::error('Zainpay Webhook verification failed for ' . $reference . ': ' . $e->getMessage());
            return null;
        }
    }
}
