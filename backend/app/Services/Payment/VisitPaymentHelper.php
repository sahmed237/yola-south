<?php

namespace App\Services\Payment;

use App\Models\Visit;
use App\Models\VisitActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\VisitConfirmedNotification;
use App\Services\Payment\DTOs\PaymentVerifyResult;

class VisitPaymentHelper
{
    /**
     * Synchronize and process payment details after verification
     */
    public static function processSuccessfulPayment(Visit $visit, PaymentVerifyResult $result)
    {
        try {
            // 1. Update visit status and payment details
            $visit->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);

            // 1b. Update the payment record
            \App\Models\VisitPayment::where('reference', $result->reference)->update([
                'status' => 'successful',
                'raw_details' => json_decode($result->rawResponse, true)
            ]);

            // 2. Log activity (Similar to auditing in InvoiceHelper)
            self::logActivity($visit, 'payment_verified', "Payment of {$result->amountPaid} verified via " . ucfirst($result->gateway) . ". Ref: {$result->reference}", [
                'reference' => $result->reference,
                'gateway' => $result->gateway,
                'amount' => $result->amountPaid
            ]);

            // 3. Send confirmation email (Matching sendPaymentConfirmationEmail pattern)
            self::sendPaymentConfirmationEmail($visit);

        } catch (\Exception $e) {
            Log::error('Error processing successful payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send payment confirmation email to the visitor
     */
    public static function sendPaymentConfirmationEmail(Visit $visit)
    {
        try {
            Mail::to($visit->email)->send(new VisitConfirmedNotification($visit));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email: ' . $e->getMessage());
            return false;
        }
    }

    private static function logActivity(Visit $visit, $action, $description, $properties = [])
    {
        VisitActivity::create([
            'visit_id' => $visit->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
