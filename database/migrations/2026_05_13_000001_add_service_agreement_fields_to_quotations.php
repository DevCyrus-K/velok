<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }

        Schema::table('quotations', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotations', 'service_agreement_path')) {
                $table->string('service_agreement_path')->nullable()->after('signature_type');
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_filename')) {
                $table->string('service_agreement_filename')->nullable()->after('service_agreement_path');
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_generated_at')) {
                $table->timestamp('service_agreement_generated_at')->nullable()->after('service_agreement_filename');
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_email_status')) {
                $table->string('service_agreement_email_status', 40)->nullable()->after('service_agreement_generated_at');
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_emailed_at')) {
                $table->timestamp('service_agreement_emailed_at')->nullable()->after('service_agreement_email_status');
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_email_failed_reason')) {
                $table->text('service_agreement_email_failed_reason')->nullable()->after('service_agreement_emailed_at');
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_email_attempts')) {
                $table->unsignedInteger('service_agreement_email_attempts')->default(0)->after('service_agreement_email_failed_reason');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }

        Schema::table('quotations', function (Blueprint $table): void {
            foreach ([
                'service_agreement_email_attempts',
                'service_agreement_email_failed_reason',
                'service_agreement_emailed_at',
                'service_agreement_email_status',
                'service_agreement_generated_at',
                'service_agreement_filename',
                'service_agreement_path',
            ] as $column) {
                if (Schema::hasColumn('quotations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
