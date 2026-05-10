<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_logs')) {
            return;
        }

        Schema::table('email_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('email_logs', 'sender_role')) {
                $table->string('sender_role', 40)->nullable()->after('emailable_id');
            }

            if (! Schema::hasColumn('email_logs', 'sender_email')) {
                $table->string('sender_email')->nullable()->after('sender_role');
            }

            if (! Schema::hasColumn('email_logs', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('sender_email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('email_logs')) {
            return;
        }

        Schema::table('email_logs', function (Blueprint $table): void {
            foreach (['sender_name', 'sender_email', 'sender_role'] as $column) {
                if (Schema::hasColumn('email_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
