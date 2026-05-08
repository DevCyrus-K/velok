<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('invoices', 'sent_to_email')) {
                $table->string('sent_to_email')->nullable()->after('sent_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'sent_to_email')) {
                $table->dropColumn('sent_to_email');
            }

            if (Schema::hasColumn('invoices', 'sent_at')) {
                $table->dropColumn('sent_at');
            }
        });
    }
};
