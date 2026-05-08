<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('sent_to_email');
            }
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('paid', 'unpaid', 'pending', 'draft', 'failed', 'sent', 'overdue', 'void', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        DB::table('invoices')
            ->whereIn('status', ['void', 'cancelled'])
            ->update(['status' => 'draft']);

        DB::table('invoices')
            ->where('status', 'overdue')
            ->update(['status' => 'unpaid']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('paid', 'unpaid', 'pending', 'draft', 'failed', 'sent') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
