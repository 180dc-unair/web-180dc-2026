<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->foreignUuid('image_id')->nullable()->constrained('media_assets')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type'); // digital / physical
            $table->string('status')->default('active');
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->integer('sold_count')->default(0);
            $table->string('digital_file_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
