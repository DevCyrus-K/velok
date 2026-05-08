<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table): void {
                if (! Schema::hasColumn('messages', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('status');
                }

                if (! Schema::hasColumn('messages', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('origin_page');
                }

                if (! Schema::hasColumn('messages', 'attachment_original_name')) {
                    $table->string('attachment_original_name')->nullable()->after('attachment_path');
                }

                if (! Schema::hasColumn('messages', 'attachment_mime')) {
                    $table->string('attachment_mime', 100)->nullable()->after('attachment_original_name');
                }

                if (! Schema::hasColumn('messages', 'email_log_id')) {
                    $table->unsignedBigInteger('email_log_id')->nullable()->index()->after('attachment_mime');
                }

                if (! Schema::hasColumn('messages', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('email_logs')) {
            Schema::table('email_logs', function (Blueprint $table): void {
                if (Schema::hasColumn('email_logs', 'status')) {
                    $table->string('status', 40)->default('queued')->change();
                }

                if (Schema::hasColumn('email_logs', 'emailable_type')) {
                    $table->string('emailable_type')->nullable()->change();
                }

                if (Schema::hasColumn('email_logs', 'emailable_id')) {
                    $table->unsignedBigInteger('emailable_id')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table): void {
            foreach ([
                'read_at',
                'attachment_path',
                'attachment_original_name',
                'attachment_mime',
                'email_log_id',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
