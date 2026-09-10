<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url_key')->unique();
            $table->text('destination_url');

            $table->unsignedInteger('clicks')->default(0);

            $table->boolean('single_use')->default(false);
            $table->timestamp('used_at')->nullable();

            $table->boolean('secure')->default(false);
            $table->unsignedSmallInteger('redirect_status_code')->default(302);

            $table->boolean('track_visits')->default(true);

            $table->timestamp('activate_at')->nullable();
            $table->timestamp('deactivate_at')->nullable();

            $table->timestamps();

            $table->index('url_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
