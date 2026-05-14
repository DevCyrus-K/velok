<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_agreements')) {
            return;
        }

        Schema::create('service_agreements', function (Blueprint $table): void {
            // Production hardening: service agreements now have first-class B2-backed records.
            $table->id();
            $table->foreignId('quote_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('customers')->cascadeOnDelete();
            $table->string('agreement_reference_no')->unique();
            $table->date('proposed_move_date');
            $table->string('pdf_storage_key', 500);
            $table->string('pdf_storage_file_id', 200);
            $table->string('pdf_storage_url', 1000)->nullable();
            $table->enum('email_status', ['pending', 'sent', 'email_failed'])->default('pending')->index();
            $table->timestamp('email_sent_at')->nullable();
            $table->unsignedTinyInteger('email_attempts')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('quote_id');
            $table->index('client_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_agreements');
    }
};
