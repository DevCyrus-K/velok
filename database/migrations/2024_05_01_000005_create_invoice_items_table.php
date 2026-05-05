<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_items')) {
            return;
        }

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('cascade');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
