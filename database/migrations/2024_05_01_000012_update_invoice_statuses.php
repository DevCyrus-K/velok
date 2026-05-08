<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        DB::table('invoices')->where('status', 'overdue')->update(['status' => 'unpaid']);
        DB::table('invoices')->where('status', 'cancelled')->update(['status' => 'draft']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('paid', 'unpaid', 'pending', 'draft', 'failed', 'sent') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        DB::table('invoices')->where('status', 'pending')->update(['status' => 'draft']);
        DB::table('invoices')->where('status', 'failed')->update(['status' => 'draft']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft', 'sent', 'paid', 'unpaid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
