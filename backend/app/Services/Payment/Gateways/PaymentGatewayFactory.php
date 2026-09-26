<?php

namespace App\Services\Payment\Gateways;

use Exception;

class PaymentGatewayFactory
{
    public static function create(string $gateway)
    {
        return match ($gateway) {
            'paystack' => new PaystackGateway(),
            'monnify'  => new MonnifyGateway(),
            'zainpay'  => new ZainpayGateway(),
            default    => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }
}
