<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('logo_id')->nullable()->constrained('media_assets')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // CASE_COLLABORATION, EVENT_COLLABORATION, MEDIA_PARTNER
            $table->string('website_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
