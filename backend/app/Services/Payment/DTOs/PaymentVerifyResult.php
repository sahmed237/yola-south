<?php

namespace App\Services\Payment\DTOs;

class PaymentVerifyResult
{
    public float $amountPaid;
    public float $fee;
    public string $paymentDate;
    public ?string $rawResponse;
    public ?string $reference;
    public string $gateway;

    public function __construct(float $amountPaid, string $paymentDate, string $gateway, ?string $rawResponse = null, ?string $reference = null, float $fee = 0.0)
    {
        $this->amountPaid = $amountPaid;
        $this->paymentDate = $paymentDate;
        $this->gateway = $gateway;
        $this->rawResponse = $rawResponse;
        $this->reference = $reference;
        $this->fee = $fee;
    }
}
