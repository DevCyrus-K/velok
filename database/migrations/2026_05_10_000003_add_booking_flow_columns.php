<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quote_requests')) {
            Schema::table('quote_requests', function (Blueprint $table): void {
                if (! Schema::hasColumn('quote_requests', 'contact_preference')) {
                    $table->enum('contact_preference', ['email', 'whatsapp', 'both'])
                        ->nullable()
                        ->default('both')
                        ->after('phone');
                }

                if (! Schema::hasColumn('quote_requests', 'whatsapp_url')) {
                    $table->text('whatsapp_url')->nullable()->after('contact_preference');
                }
            });
        }

        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table): void {
                if (! Schema::hasColumn('quotations', 'approval_token')) {
                    $table->string('approval_token')->nullable()->unique()->after('approval_date');
                }

                if (! Schema::hasColumn('quotations', 'approval_token_expires_at')) {
                    $table->timestamp('approval_token_expires_at')->nullable()->after('approval_token');
                }

                if (! Schema::hasColumn('quotations', 'pdf_token')) {
                    $table->string('pdf_token')->nullable()->unique()->after('approval_token_expires_at');
                }

                if (! Schema::hasColumn('quotations', 'approved_by_name')) {
                    $table->string('approved_by_name')->nullable()->after('pdf_token');
                }

                if (! Schema::hasColumn('quotations', 'approval_ip')) {
                    $table->string('approval_ip')->nullable()->after('approved_by_name');
                }

                if (! Schema::hasColumn('quotations', 'approval_method')) {
                    $table->string('approval_method')->nullable()->after('approval_ip');
                }

                if (! Schema::hasColumn('quotations', 'sent_via')) {
                    $table->string('sent_via')->nullable()->after('sent_at');
                }

                if (! Schema::hasColumn('quotations', 'deposit_amount')) {
                    $table->decimal('deposit_amount', 10, 2)->nullable()->after('sent_via');
                }

                if (! Schema::hasColumn('quotations', 'deposit_paid')) {
                    $table->boolean('deposit_paid')->default(false)->after('deposit_amount');
                }

                if (! Schema::hasColumn('quotations', 'deposit_paid_at')) {
                    $table->timestamp('deposit_paid_at')->nullable()->after('deposit_paid');
                }

                if (! Schema::hasColumn('quotations', 'deposit_reference')) {
                    $table->string('deposit_reference')->nullable()->after('deposit_paid_at');
                }

                if (! Schema::hasColumn('quotations', 'deposit_method')) {
                    $table->string('deposit_method')->nullable()->after('deposit_reference');
                }

                if (! Schema::hasColumn('quotations', 'deposit_whatsapp_url')) {
                    $table->text('deposit_whatsapp_url')->nullable()->after('deposit_method');
                }

                if (! Schema::hasColumn('quotations', 'reminder_whatsapp_url')) {
                    $table->text('reminder_whatsapp_url')->nullable()->after('deposit_whatsapp_url');
                }

                if (! Schema::hasColumn('quotations', 'followup_whatsapp_url')) {
                    $table->text('followup_whatsapp_url')->nullable()->after('reminder_whatsapp_url');
                }
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table): void {
                if (! Schema::hasColumn('invoices', 'sent_via')) {
                    $table->string('sent_via')->nullable()->after('sent_at');
                }
            });
        }

        if (! Schema::hasTable('booking_stages')) {
            Schema::create('booking_stages', function (Blueprint $table): void {
                $table->id();
                $table->string('stageable_type');
                $table->unsignedBigInteger('stageable_id');
                $table->string('stage');
                $table->text('description')->nullable();
                $table->enum('triggered_by', ['system', 'admin', 'customer'])->default('system');
                $table->string('actor_name')->nullable();
                $table->string('actor_ip')->nullable();
                $table->string('channel')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['stageable_type', 'stageable_id']);
                $table->index('stage');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_stages');

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'sent_via')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->dropColumn('sent_via');
            });
        }

        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table): void {
                $columns = [
                    'approval_token',
                    'approval_token_expires_at',
                    'pdf_token',
                    'approved_by_name',
                    'approval_ip',
                    'approval_method',
                    'sent_via',
                    'deposit_amount',
                    'deposit_paid',
                    'deposit_paid_at',
                    'deposit_reference',
                    'deposit_method',
                    'deposit_whatsapp_url',
                    'reminder_whatsapp_url',
                    'followup_whatsapp_url',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('quotations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('quote_requests')) {
            Schema::table('quote_requests', function (Blueprint $table): void {
                foreach (['whatsapp_url', 'contact_preference'] as $column) {
                    if (Schema::hasColumn('quote_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
