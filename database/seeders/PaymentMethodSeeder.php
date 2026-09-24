<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::query()
            ->whereNotIn('gateway', ['manual', 'midtrans'])
            ->update(['is_active' => false]);

        $methods = [
            [
                'name' => 'Manual Transfer BCA',
                'code' => 'manual_bca',
                'gateway' => 'manual',
                'is_active' => true,
            ],
            [
                'name' => 'Midtrans VA BCA',
                'code' => 'midtrans_va_bca',
                'gateway' => 'midtrans',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                $method,
            );
        }
    }
}
