<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method_id');
            $table->uuid('idempotency_key')->nullable()->after('gateway');
            $table->unique(['order_id', 'idempotency_key']);
            $table->unique('gateway_reference', 'payments_gateway_reference_unique');
            $table->index(['status', 'expired_at']);
        });

        Schema::table('payment_webhooks', function (Blueprint $table) {
            $table->string('processing_error')->nullable();
            $table->index(['gateway', 'is_processed']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_gateway_reference_unique');
            $table->dropUnique(['order_id', 'idempotency_key']);
            $table->dropIndex(['status', 'expired_at']);
            $table->dropColumn(['gateway', 'idempotency_key']);
        });

        Schema::table('payment_webhooks', function (Blueprint $table) {
            $table->dropIndex(['gateway', 'is_processed']);
            $table->dropColumn('processing_error');
        });
    }
};
