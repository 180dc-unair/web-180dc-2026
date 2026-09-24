<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id', 'orders_user_id_index');
            $table->index('status', 'orders_status_index');
            $table->index('order_number', 'orders_order_number_index');
            $table->index('expired_at', 'orders_expired_at_index');
            $table->string('idempotency_key')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_index');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_order_number_index');
            $table->dropIndex('orders_expired_at_index');
            $table->dropColumn('idempotency_key');
        });
    }
};
