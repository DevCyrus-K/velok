<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quote_requests')) {
            return;
        }

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('moving_from')->nullable();
            $table->string('moving_to')->nullable();
            $table->date('move_date')->nullable();
            $table->string('service_type')->nullable();
            $table->string('move_size')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('source_page')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['new', 'emailed', 'email_failed', 'processing', 'quoted', 'closed', 'spam'])->default('new');
            $table->timestamp('created_at')->nullable();
            $table->index('email');
            $table->index('phone');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
