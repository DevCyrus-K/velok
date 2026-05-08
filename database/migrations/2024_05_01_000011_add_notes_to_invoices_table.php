<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices') || Schema::hasColumn('invoices', 'notes')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices') || !Schema::hasColumn('invoices', 'notes')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
