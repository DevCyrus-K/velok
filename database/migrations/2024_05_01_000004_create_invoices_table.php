<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            return;
        }

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('quote_request_id')->nullable();
            $table->foreign('quote_request_id')
                ->references('id')
                ->on('quote_requests')
                ->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('move_origin')->nullable();
            $table->string('move_destination')->nullable();
            $table->date('move_date')->nullable();
            $table->string('move_size')->nullable();
            $table->string('quote_reference')->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['paid', 'unpaid', 'pending', 'draft', 'failed', 'sent'])->default('draft');
            $table->string('payment_method')->nullable();
            $table->timestamps();
            $table->index('invoice_number');
            $table->index('quote_request_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
