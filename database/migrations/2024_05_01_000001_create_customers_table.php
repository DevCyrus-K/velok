<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            return;
        }

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('contact_key')->unique();
            $table->unsignedBigInteger('source_quote_request_id')->nullable();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('moving_from')->nullable();
            $table->string('moving_to')->nullable();
            $table->string('latest_service_type')->nullable();
            $table->integer('quotes_count')->default(0);
            $table->integer('approved_quotes_count')->default(0);
            $table->integer('declined_quotes_count')->default(0);
            $table->enum('status', ['lead', 'active_client', 'completed', 'inactive'])->default('lead');
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_quote_at')->nullable();
            $table->timestamps();
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
