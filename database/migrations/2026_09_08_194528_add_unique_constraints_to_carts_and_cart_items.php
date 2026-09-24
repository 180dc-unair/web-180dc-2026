<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unique('user_id', 'carts_user_id_unique');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id'], 'cart_items_cart_product_unique');
            $table->index('cart_id', 'cart_items_cart_id_index');
            $table->index('product_id', 'cart_items_product_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_id_unique');
        });
        
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_cart_product_unique');
            $table->dropIndex('cart_items_cart_id_index');
            $table->dropIndex('cart_items_product_id_index');
        });
    }
};
