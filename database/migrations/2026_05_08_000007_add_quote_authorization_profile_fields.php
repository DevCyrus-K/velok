<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quote_requests') && ! Schema::hasColumn('quote_requests', 'approval_date')) {
            Schema::table('quote_requests', function (Blueprint $table) {
                $table->date('approval_date')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('quotations') && ! Schema::hasColumn('quotations', 'cancellation_policy')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->text('cancellation_policy')->nullable()->after('cancellation_notice_hours');
            });
        }

        if (Schema::hasTable('users')) {
            $needsJobTitle = ! Schema::hasColumn('users', 'job_title');
            $needsSignature = ! Schema::hasColumn('users', 'signature');

            if ($needsJobTitle || $needsSignature) {
                Schema::table('users', function (Blueprint $table) use ($needsJobTitle, $needsSignature) {
                    if ($needsJobTitle) {
                        $table->string('job_title')->nullable();
                    }

                    if ($needsSignature) {
                        $table->string('signature')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quote_requests') && Schema::hasColumn('quote_requests', 'approval_date')) {
            Schema::table('quote_requests', function (Blueprint $table) {
                $table->dropColumn('approval_date');
            });
        }

        if (Schema::hasTable('quotations') && Schema::hasColumn('quotations', 'cancellation_policy')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('cancellation_policy');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'signature')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('signature');
            });
        }
    }
};
