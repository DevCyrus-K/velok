<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Production hardening: admin middleware can now check an explicit user flag.
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_admin')->default(true)->after('remember_token')->index();
            });
        }

        foreach (['customers', 'quote_requests', 'quotations', 'invoices', 'career_jobs', 'job_applications'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                // Production hardening: important business records are recoverable instead of being hard-deleted.
                $table->softDeletes()->after(Schema::hasColumn($tableName, 'updated_at') ? 'updated_at' : 'created_at');
            });
        }

        $this->addIndexIfMissing('customers', ['created_at']);
        $this->addIndexIfMissing('quote_requests', ['created_at']);
        $this->addIndexIfMissing('quotations', ['created_at']);
        $this->addIndexIfMissing('invoices', ['customer_email']);
        $this->addIndexIfMissing('invoices', ['created_at']);
        $this->addIndexIfMissing('messages', ['created_at']);
        $this->addIndexIfMissing('career_jobs', ['status']);
        $this->addIndexIfMissing('career_jobs', ['created_at']);
        $this->addIndexIfMissing('job_applications', ['status']);
        $this->addIndexIfMissing('job_applications', ['created_at']);
        $this->addIndexIfMissing('email_logs', ['recipient_email']);
        $this->addIndexIfMissing('email_logs', ['status']);
        $this->addIndexIfMissing('email_logs', ['created_at']);
    }

    public function down(): void
    {
        foreach (['customers', 'quote_requests', 'quotations', 'invoices', 'career_jobs', 'job_applications'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropSoftDeletes();
                });
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('is_admin');
            });
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndexIfMissing(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        $indexName = $tableName.'_'.implode('_', $columns).'_production_index';

        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        return collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $indexName);
    }
};
