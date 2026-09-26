<?php

namespace App\Services\Payment\Contracts;

use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;

interface PaymentGatewayInterface
{
    public function initialize($invoice, string $email, string $phone, string $callbackUrl) : PaymentInitResult;

    public function verify(string $reference) : PaymentVerifyResult;

    public function webhook(array $payload, array $headers, string $rawBody) : ?PaymentVerifyResult;
}
