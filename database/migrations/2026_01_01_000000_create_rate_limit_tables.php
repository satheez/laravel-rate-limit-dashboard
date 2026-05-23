<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rate_limit_configs', function (Blueprint $table): void {
            $table->string('limiter_name')->primary();
            $table->integer('max_attempts');
            $table->integer('decay_seconds');
            $table->json('overrides')->nullable();
            $table->integer('alert_threshold')->default(80);
            $table->timestamps();
        });

        Schema::create('rate_limit_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('limiter_name');
            $table->string('limiter_key');
            $table->integer('max_attempts');
            $table->integer('current_attempts');
            $table->string('request_method')->nullable();
            $table->string('url_path')->nullable();
            $table->enum('status', ['hit', 'throttled']);
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('api_token')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['limiter_name', 'created_at']);
            $table->index(['ip_address']);
            $table->index(['user_id']);
        });

        Schema::create('rate_limit_history_summaries', function (Blueprint $table): void {
            $table->id();
            $table->string('limiter_name');
            $table->string('time_window'); // minute, hour, day
            $table->integer('total_requests')->default(0);
            $table->integer('throttled_requests')->default(0);
            $table->timestamp('window_start');
            $table->timestamps();

            $table->unique(['limiter_name', 'time_window', 'window_start'], 'rate_limit_summary_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limit_history_summaries');
        Schema::dropIfExists('rate_limit_events');
        Schema::dropIfExists('rate_limit_configs');
    }
};
