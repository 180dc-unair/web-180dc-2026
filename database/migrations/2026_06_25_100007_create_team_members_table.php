<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('image_id')->nullable()->constrained('media_assets')->onDelete('set null');
            $table->foreignUuid('division_id')->nullable()->constrained('team_divisions')->onDelete('set null');
            $table->foreignUuid('position_id')->nullable()->constrained('team_positions')->onDelete('set null');
            $table->foreignUuid('period_id')->nullable()->constrained('team_periods')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
