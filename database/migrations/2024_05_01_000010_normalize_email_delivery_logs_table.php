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
            DB::statement("ALTER TABLE email_delivery_logs MODIFY direction VARCHAR(50) NOT NULL DEFAULT 'outbound'");
            DB::statement("ALTER TABLE email_delivery_logs MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            DB::statement('ALTER TABLE email_delivery_logs MODIFY recipient_email VARCHAR(190) NULL');
            DB::statement('ALTER TABLE email_delivery_logs MODIFY subject VARCHAR(190) NULL');
        }

        Schema::table('email_delivery_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('email_delivery_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_delivery_logs') || !Schema::hasColumn('email_delivery_logs', 'updated_at')) {
            return;
        }

        Schema::table('email_delivery_logs', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
