<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     */
    protected $description = 'Expire pending orders that have passed their expiration time';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $count = $orderService->expirePending();

        $this->info("Expired {$count} pending order(s).");

        return Command::SUCCESS;
    }
}
