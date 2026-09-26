<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question'     => 'How do I verify if my payment is recorded?',
                'answer'       => 'Once a payment is successfully completed via our gateway, the database is instantly updated. You can enter your business name on the homepage lookup to confirm your active receipt status. The download receipt contains a secure QR verification indicator.',
                'sort_order'   => 1,
                'is_published' => true,
            ],
            [
                'question'     => 'What happens if a transaction callback fails?',
                'answer'       => 'If a callback fails due to gateway issues or network latency, system officers can perform a manual "Verify Payment" recheck on the invoice detail page in their dashboard. This contacts the gateway API to safely update status codes instantly.',
                'sort_order'   => 2,
                'is_published' => true,
            ],
            [
                'question'     => 'How is the portal service fee calculated?',
                'answer'       => 'Service fees are automatically split out during payment processing based on the configured service fee rules. The split mechanism route-diverts this net amount directly to the service provider\'s subaccount while delivering the primary revenue portion directly to the primary government ledger.',
                'sort_order'   => 3,
                'is_published' => true,
            ],
            [
                'question'     => 'Can I pay for multiple revenue rules at once?',
                'answer'       => 'Yes. System Invoices allow grouping multiple revenue items into a single checkout session. Payment splits are processed atomically, ensuring each agency subaccount receives its correct share.',
                'sort_order'   => 4,
                'is_published' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            \App\Models\Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
