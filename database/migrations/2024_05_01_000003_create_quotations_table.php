<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quotations')) {
            return;
        }

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_request_id');
            $table->foreign('quote_request_id')
                ->references('id')
                ->on('quote_requests')
                ->onDelete('cascade');
            $table->string('company_name');
            $table->string('company_email');
            $table->string('company_phone');
            $table->string('company_website')->nullable();
            $table->date('quote_date');
            $table->date('quote_valid_until')->nullable();
            $table->decimal('deposit_percentage', 5, 2)->nullable();
            $table->integer('cancellation_notice_hours')->nullable();
            $table->json('services_included')->nullable();
            $table->text('additional_notes')->nullable();
            $table->text('payment_terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'declined', 'expired'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('moving_from')->nullable();
            $table->string('moving_to')->nullable();
            $table->date('move_date')->nullable();
            $table->decimal('quote_amount', 10, 2)->nullable();
            $table->string('authorized_by')->nullable();
            $table->string('authorized_role')->nullable();
            $table->date('approval_date')->nullable();
            $table->text('signature')->nullable();
            $table->string('signature_type')->nullable();
            $table->timestamps();
            $table->index('quote_request_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
