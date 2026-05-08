<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
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

                if (! Schema::hasColumn('email_logs', 'tracking_token')) {
                    $table->uuid('tracking_token')->nullable()->unique()->after('status');
                }

                if (! Schema::hasColumn('email_logs', 'opened_at')) {
                    $table->timestamp('opened_at')->nullable()->after('sent_at');
                }

                if (! Schema::hasColumn('email_logs', 'failed_reason')) {
                    $table->text('failed_reason')->nullable()->after('opened_at');
                }

                if (! Schema::hasColumn('email_logs', 'attempts')) {
                    $table->unsignedInteger('attempts')->default(0)->after('failed_reason');
                }
            });

            DB::table('email_logs')
                ->whereNull('tracking_token')
                ->orderBy('id')
                ->chunkById(100, function ($logs): void {
                    foreach ($logs as $log) {
                        DB::table('email_logs')
                            ->where('id', $log->id)
                            ->update(['tracking_token' => (string) Str::uuid()]);
                    }
                });

            return;
        }

        Schema::create('email_logs', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('emailable');
            $table->string('recipient_email')->index();
            $table->string('subject')->nullable();
            $table->string('status', 40)->default('queued')->index();
            $table->uuid('tracking_token')->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->text('failed_reason')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
