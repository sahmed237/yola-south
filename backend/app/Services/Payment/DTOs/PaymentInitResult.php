<?php

namespace App\Services\Payment\DTOs;

class PaymentInitResult
{
    public string $reference;
    public string $redirectUrl;
    public ?string $rawResponse;

    public function __construct(string $reference, string $redirectUrl, ?string $rawResponse = null)
    {
        $this->reference = $reference;
        $this->redirectUrl = $redirectUrl;
        $this->rawResponse = $rawResponse;
    }
}
