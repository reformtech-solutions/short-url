<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_url_visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('short_url_id')
                ->constrained('short_urls')
                ->cascadeOnDelete();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->text('referer_url')->nullable();

            $table->timestamp('visited_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_url_visits');
    }
};
