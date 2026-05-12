<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quote_requests') || ! Schema::hasColumn('quote_requests', 'status')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE quote_requests MODIFY status ENUM('new','emailed','email_failed','processing','quoted','created','completed','closed','spam') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('quote_requests') || ! Schema::hasColumn('quote_requests', 'status')) {
            return;
        }

        DB::table('quote_requests')
            ->where('status', 'completed')
            ->update(['status' => 'created']);

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE quote_requests MODIFY status ENUM('new','emailed','email_failed','processing','quoted','created','closed','spam') NOT NULL DEFAULT 'new'");
    }
};
