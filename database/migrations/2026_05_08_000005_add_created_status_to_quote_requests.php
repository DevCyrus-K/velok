<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quote_requests') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE quote_requests MODIFY status ENUM('new','emailed','email_failed','processing','quoted','created','closed','spam') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('quote_requests') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('quote_requests')
            ->where('status', 'created')
            ->update(['status' => 'quoted']);

        DB::statement("ALTER TABLE quote_requests MODIFY status ENUM('new','emailed','email_failed','processing','quoted','closed','spam') NOT NULL DEFAULT 'new'");
    }
};
