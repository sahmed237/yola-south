<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;
use App\Services\Payment\PaymentApi\PaystackApi;
use Illuminate\Support\Facades\Log;
use Exception;

class PaystackGateway implements PaymentGatewayInterface
{
    public function initialize($invoice, string $email, string $phone, string $callbackUrl) : PaymentInitResult
    {
        $invoiceId = $invoice->id ?? $invoice['id'];
        $amount = (float) ($invoice->total_amount ?? $invoice->payment_amount ?? $invoice['total_amount'] ?? $invoice['payment_amount'] ?? 0);
        $baseRef = $invoice->reference ?? $invoice['reference'] ?? ('INV-' . $invoiceId);
        $reference = $baseRef . '-' . time();

        $subaccounts = [];
        if ($invoice) {
            $splits = isset($invoice->splits) ? $invoice->splits : \App\Models\InvoiceSplit::where('invoice_id', $invoiceId)->get();
            foreach ($splits as $split) {
                if ($split->subaccount_code && $split->subaccount_code !== 'NO_SUBACCOUNT') {
                    $subaccounts[] = [
                        'subaccount' => $split->subaccount_code,
                        'share' => (int) round($split->amount * 100), // Flat amount in kobo
                    ];
                }
            }
        }

        // Resolve and sanitize email for Paystack requirement
        $cleanEmail = trim($email ?: '');
        if (empty($cleanEmail) || !filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            $candidateEmail = $invoice->email ?? null;
            if (!empty($candidateEmail) && filter_var(trim($candidateEmail), FILTER_VALIDATE_EMAIL)) {
                $cleanEmail = trim($candidateEmail);
            } else {
                $identifier = preg_replace('/[^a-zA-Z0-9]/', '', $phone ?: ($reference ?: ('inv' . $invoiceId)));
                $cleanEmail = 'taxpayer.' . strtolower($identifier ?: rand(100000, 999999)) . '@yolasouth.lg.gov.ng';
            }
        }

        try {
            $api = PaystackApi::getInstance();
            
            $payload = [
                'amount' => (int) round($amount * 100),
                'email' => $cleanEmail,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'invoice_id' => $invoiceId,
                    'phone' => $phone
                ]
            ];

            if (count($subaccounts) > 0) {
                $payload['split'] = [
                    'type' => 'flat',
                    'bearer_type' => 'all',
                    'subaccounts' => $subaccounts,
                ];
            }

            $response = $api->post('transaction/initialize', $payload);

            if (isset($response['status']) && $response['status'] === true) {
                $data = $response['data'];
                return new PaymentInitResult(
                    reference: $reference,
                    redirectUrl: $data['authorization_url'],
                    rawResponse: json_encode($response)
                );
            }

            throw new Exception('Paystack initialization failed: ' . ($response['message'] ?? 'Unknown error'));

        } catch (\Throwable $e) {
            throw new Exception('Paystack initialization failed: ' . $e->getMessage());
        }
    }

    public function verify(string $reference) : PaymentVerifyResult
    {
        if (empty($reference)) {
            throw new Exception('Transaction reference is required.');
        }

        try {
            $api = PaystackApi::getInstance();
            $response = $api->get('transaction/verify/' . $reference);

            if (isset($response['status']) && $response['status'] === true) {
                $data = $response['data'];
                
                if (($data['status'] ?? '') !== 'success') {
                     // Return 0 amount paid instead of throwing exception for ongoing/failed payments
                     return new PaymentVerifyResult(
                         amountPaid: 0,
                         paymentDate: date('Y-m-d H:i:s'),
                         gateway: 'paystack',
                         rawResponse: json_encode($data),
                         reference: $reference
                     );
                }

                $fee = (float) (($data['fees'] ?? 0) / 100);

                return new PaymentVerifyResult(
                    amountPaid: (float) (($data['amount'] ?? 0) / 100),
                    paymentDate: $data['paid_at'] ?? date('Y-m-d H:i:s'),
                    gateway: 'paystack',
                    rawResponse: json_encode($data),
                    reference: $reference,
                    fee: $fee
                );
            }

            throw new Exception($response['message'] ?? 'Transaction not found');

        } catch (\Throwable $e) {
            Log::error('Paystack verification error: ' . $e->getMessage());
            throw new Exception('Unable to verify transaction: ' . $e->getMessage());
        }
    }

    public function webhook(array $payload, array $headers, string $rawBody) : ?PaymentVerifyResult
    {
        $signature = $headers['x-paystack-signature'] ?? null;
        if (is_array($signature)) $signature = $signature[0];

        $secretKey = config('services.paystack.secret_key');

        if (!$signature || !$secretKey) {
            return null;
        }

        $computedSignature = hash_hmac('sha512', $rawBody, $secretKey);

        if (!hash_equals($computedSignature, $signature)) {
            return null;
        }

        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return null;
        }

        try {
            return $this->verify($reference);
        } catch (\Exception $e) {
            Log::error('Paystack Webhook verification failed for ' . $reference . ': ' . $e->getMessage());
            return null;
        }
    }
}
