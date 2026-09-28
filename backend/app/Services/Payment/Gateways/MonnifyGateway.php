<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;
use App\Services\Payment\PaymentApi\MonnifyApi;
use Illuminate\Support\Facades\Log;
use Exception;

class MonnifyGateway implements PaymentGatewayInterface
{
    public function initialize($invoice, string $email, string $phone, string $callbackUrl): PaymentInitResult
    {
        $invoiceId = $invoice->id ?? $invoice['id'];
        $amount = (float) ($invoice->total_amount ?? $invoice->payment_amount ?? $invoice['total_amount'] ?? $invoice['payment_amount'] ?? 0);
        $contractCode = \App\Models\Setting::where('key', 'monnify_contract_code')->value('value') ?: config('services.monnify.contract_code');
        $baseRef = $invoice->reference ?? $invoice['reference'] ?? ('INV-' . $invoiceId);
        $reference = $baseRef . '-' . time();

        // Resolve customer name and description
        $customerName = 'Taxpayer';
        $paymentDescription = 'Unified Payment for Invoice #' . $invoiceId;
        if ($invoice instanceof \App\Models\Invoice) {
            if ($invoice->invoiceable instanceof \App\Models\ShopAllocation) {
                $customerName = $invoice->invoiceable->applicant_name;
                $paymentDescription = 'Shop Allocation #' . $invoice->invoiceable->application_no . ' Invoice';
            } elseif ($invoice->establishment) {
                $customerName = $invoice->establishment->occupant->name ?? $invoice->establishment->name ?? 'Taxpayer';
            }
        }

        $incomeSplitConfig = [];
        if ($invoice) {
            $splits = isset($invoice->splits) ? $invoice->splits : \App\Models\InvoiceSplit::where('invoice_id', $invoiceId)->get();
            
            // Filter splits that actually have a subaccount code
            $validSplits = [];
            foreach ($splits as $split) {
                if ($split->subaccount_code && $split->subaccount_code !== 'NO_SUBACCOUNT') {
                    $validSplits[] = $split;
                }
            }
            
            $totalSubaccountAmount = array_sum(array_map(fn($s) => (float)$s->amount, $validSplits));
            $totalValidCount = count($validSplits);
            $currentSum = 0.0;
            
            foreach ($validSplits as $index => $split) {
                if ($totalSubaccountAmount > 0) {
                    if ($index === $totalValidCount - 1) {
                        $feePercentage = max(0.0, 100.0 - $currentSum);
                    } else {
                        $feePercentage = round(($split->amount / $totalSubaccountAmount) * 100, 2);
                        $currentSum += $feePercentage;
                    }
                } else {
                    $feePercentage = round(100.0 / $totalValidCount, 2);
                }

                $incomeSplitConfig[] = [
                    'subAccountCode' => $split->subaccount_code,
                    'splitAmount' => (float) $split->amount,
                    'feeBearer' => true,
                    'feePercentage' => $feePercentage,
                ];
            }
        }

        // Resolve and sanitize customer email for Monnify requirement
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
            $api = MonnifyApi::getInstance();
            
            $payload = [
                'amount' => $amount,
                'customerName' => $customerName,
                'customerEmail' => $cleanEmail,
                'paymentReference' => $reference,
                'paymentDescription' => $paymentDescription,
                'currencyCode' => 'NGN',
                'contractCode' => $contractCode,
                'redirectUrl' => $callbackUrl,
                'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER']
            ];

            if (count($incomeSplitConfig) > 0) {
                $payload['incomeSplitConfig'] = $incomeSplitConfig;
            }

            $response = $api->post('api/v1/merchant/transactions/init-transaction', $payload);

            if (isset($response['requestSuccessful']) && $response['requestSuccessful'] === true) {
                $data = $response['responseBody'];
                return new PaymentInitResult(
                    reference: $reference,
                    redirectUrl: $data['checkoutUrl'],
                    rawResponse: json_encode($response)
                );
            }

            throw new Exception('Monnify initialization failed: ' . ($response['responseMessage'] ?? 'Unknown error'));

        } catch (\Throwable $e) {
            throw new Exception('Monnify initialization failed: ' . $e->getMessage());
        }
    }

    public function verify(string $reference): PaymentVerifyResult
    {
        if (empty($reference)) {
            throw new Exception('Transaction reference is required.');
        }

        try {
            $api = MonnifyApi::getInstance();
            $response = $api->get('api/v1/merchant/transactions/query', [
                'paymentReference' => $reference
            ]);

            if (isset($response['requestSuccessful']) && $response['requestSuccessful'] === true) {
                $data = $response['responseBody'];

                if (($data['paymentStatus'] ?? '') !== 'PAID') {
                    // Return 0 amount paid instead of throwing exception for pending payments
                    return new PaymentVerifyResult(
                        amountPaid: 0,
                        paymentDate: date('Y-m-d H:i:s'),
                        gateway: 'monnify',
                        rawResponse: json_encode($data),
                        reference: $reference
                    );
                }

                return new PaymentVerifyResult(
                    amountPaid: (float) ($data['amountPaid'] ?? 0),
                    paymentDate: $data['completedOn'] ?? date('Y-m-d H:i:s'),
                    gateway: 'monnify',
                    rawResponse: json_encode($data),
                    reference: $reference,
                    fee: (float) ($data['paymentFee'] ?? 0)
                );
            }

            throw new Exception($response['responseMessage'] ?? 'Transaction not found');

        } catch (\Throwable $e) {
            Log::error('Monnify verification error: ' . $e->getMessage());
            throw new Exception('Unable to verify Monnify transaction: ' . $e->getMessage());
        }
    }

    public function webhook(array $payload, array $headers, string $rawBody): ?PaymentVerifyResult
    {
        $signature = $headers['monnify-signature'][0] ?? $headers['Monnify-Signature'][0] ?? null;
        $secretKey = \App\Models\Setting::where('key', 'monnify_secret_key')->value('value') ?: config('services.monnify.secret_key');

        if (!$signature || !$secretKey) {
            return null;
        }

        $computedSignature = hash_hmac('sha512', $rawBody, $secretKey);

        if (!hash_equals($computedSignature, $signature)) {
            return null;
        }

        $reference = $payload['paymentReference'] ?? null;

        if (!$reference) {
            return null;
        }

        try {
            return $this->verify($reference);
        } catch (\Exception $e) {
            Log::error('Monnify Webhook verification failed for ' . $reference . ': ' . $e->getMessage());
            return null;
        }
    }
}
