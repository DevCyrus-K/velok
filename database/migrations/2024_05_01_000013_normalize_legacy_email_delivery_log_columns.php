<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_delivery_logs')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE email_delivery_logs MODIFY direction VARCHAR(50) NOT NULL DEFAULT 'client'");
            DB::statement("ALTER TABLE email_delivery_logs MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            DB::statement('ALTER TABLE email_delivery_logs MODIFY recipient_email VARCHAR(190) NULL');
            DB::statement('ALTER TABLE email_delivery_logs MODIFY subject VARCHAR(190) NULL');
            DB::statement("ALTER TABLE email_delivery_logs MODIFY transport VARCHAR(50) NOT NULL DEFAULT 'smtp'");
        }

        Schema::table('email_delivery_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('email_delivery_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_delivery_logs')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::table('email_delivery_logs')
                ->whereNotIn('direction', ['client', 'admin'])
                ->update(['direction' => 'client']);
            DB::table('email_delivery_logs')
                ->whereNotIn('status', ['sent', 'failed'])
                ->update(['status' => 'failed']);

            DB::statement("ALTER TABLE email_delivery_logs MODIFY direction ENUM('client', 'admin') NOT NULL");
            DB::statement("ALTER TABLE email_delivery_logs MODIFY status ENUM('sent', 'failed') NOT NULL");
            DB::statement('ALTER TABLE email_delivery_logs MODIFY recipient_email VARCHAR(190) NOT NULL');
            DB::statement('ALTER TABLE email_delivery_logs MODIFY subject VARCHAR(190) NOT NULL');
        }

        if (Schema::hasColumn('email_delivery_logs', 'updated_at')) {
            Schema::table('email_delivery_logs', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};
