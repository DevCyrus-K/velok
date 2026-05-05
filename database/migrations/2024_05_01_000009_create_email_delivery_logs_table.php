<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_delivery_logs')) {
            return;
        }

        Schema::create('email_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->string('form_type')->default('contact')->index();
            $table->string('recipient_email')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('direction')->default('outbound')->index();
            $table->string('subject')->nullable();
            $table->string('transport')->nullable();
            $table->text('response_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_delivery_logs');
    }
};
