<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_id');
            $table->string('url');
            $table->timestamps();
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('media_assets');
    }
};
