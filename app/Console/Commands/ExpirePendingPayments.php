<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class ExpirePendingPayments extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'Expire pending payments that have passed their expiry time';

    public function handle(PaymentService $paymentService): int
    {
        $paymentCount = $paymentService->expirePending();

        $this->info("Expired {$paymentCount} pending payment(s).");

        return self::SUCCESS;
    }
}
