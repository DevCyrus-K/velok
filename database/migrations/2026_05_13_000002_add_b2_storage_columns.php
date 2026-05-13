<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, list<string>>
     */
    private array $imageTables = [
        'users' => ['avatar_path', 'signature_path', 'signature'],
        'gallery' => ['image_path', 'image_url'],
        'reviews' => ['photo_path'],
        'messages' => ['attachment_path'],
        'quotations' => ['signature'],
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $pdfTables = [
        'messages' => ['attachment_path'],
        'quotations' => ['service_agreement_path', 'quote_pdf_storage_key'],
        'invoices' => ['storage_key'],
        'job_applications' => ['resume_url'],
    ];

    public function up(): void
    {
        foreach (array_keys($this->imageTables) as $tableName) {
            $this->addImageColumns($tableName);
        }

        foreach (array_keys($this->pdfTables) as $tableName) {
            $this->addPdfColumns($tableName);
        }

        foreach (array_unique([...array_keys($this->imageTables), ...array_keys($this->pdfTables)]) as $tableName) {
            $this->addCompatibilityColumns($tableName);
        }

        $this->addQuotationSpecificPdfColumns();
        $this->backfillLegacyPaths();
    }

    public function down(): void
    {
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table): void {
                foreach ([
                    'service_agreement_storage_file_id',
                    'quote_pdf_storage_file_id',
                    'quote_pdf_storage_url',
                    'quote_pdf_storage_key',
                ] as $column) {
                    if (Schema::hasColumn('quotations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        foreach (array_unique([...array_keys($this->imageTables), ...array_keys($this->pdfTables)]) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                foreach ([
                    'legacy_file_path',
                    'storage_url',
                    'storage_key',
                    'legacy_pdf_path',
                    'pdf_storage_url',
                    'pdf_storage_file_id',
                    'pdf_storage_key',
                    'legacy_image_path',
                    'image_public_id',
                ] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function addImageColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'image_url')) {
                $table->string('image_url', 1000)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'image_public_id')) {
                $table->string('image_public_id', 500)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'legacy_image_path')) {
                $table->string('legacy_image_path', 500)->nullable();
            }
        });
    }

    private function addPdfColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'pdf_storage_key')) {
                $table->string('pdf_storage_key', 500)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'pdf_storage_file_id')) {
                $table->string('pdf_storage_file_id', 200)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'pdf_storage_url')) {
                $table->string('pdf_storage_url', 1000)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'legacy_pdf_path')) {
                $table->string('legacy_pdf_path', 500)->nullable();
            }
        });
    }

    private function addCompatibilityColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'storage_key')) {
                $table->string('storage_key', 500)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'storage_url')) {
                $table->string('storage_url', 1000)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'legacy_file_path')) {
                $table->string('legacy_file_path', 1000)->nullable();
            }
        });
    }

    private function addQuotationSpecificPdfColumns(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }

        Schema::table('quotations', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotations', 'quote_pdf_storage_key')) {
                $table->string('quote_pdf_storage_key', 500)->nullable();
            }

            if (! Schema::hasColumn('quotations', 'quote_pdf_storage_file_id')) {
                $table->string('quote_pdf_storage_file_id', 200)->nullable();
            }

            if (! Schema::hasColumn('quotations', 'quote_pdf_storage_url')) {
                $table->string('quote_pdf_storage_url', 1000)->nullable();
            }

            if (! Schema::hasColumn('quotations', 'service_agreement_storage_file_id')) {
                $table->string('service_agreement_storage_file_id', 200)->nullable();
            }
        });
    }

    private function backfillLegacyPaths(): void
    {
        foreach ($this->imageTables as $tableName => $columns) {
            $this->backfillColumn($tableName, 'legacy_image_path', $columns);
        }

        foreach ($this->pdfTables as $tableName => $columns) {
            $this->backfillColumn($tableName, 'legacy_pdf_path', $columns);
        }

        foreach (array_unique([...array_keys($this->imageTables), ...array_keys($this->pdfTables)]) as $tableName) {
            $this->backfillColumn($tableName, 'legacy_file_path', [
                ...($this->imageTables[$tableName] ?? []),
                ...($this->pdfTables[$tableName] ?? []),
            ]);
        }
    }

    /**
     * @param  list<string>  $sourceColumns
     */
    private function backfillColumn(string $tableName, string $targetColumn, array $sourceColumns): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $targetColumn)) {
            return;
        }

        $availableColumns = collect($sourceColumns)
            ->filter(fn (string $column): bool => Schema::hasColumn($tableName, $column))
            ->map(fn (string $column): string => "NULLIF({$column}, '')")
            ->values();

        if ($availableColumns->isEmpty()) {
            return;
        }

        $expression = $availableColumns->count() > 1
            ? 'COALESCE('.$availableColumns->implode(', ').')'
            : $availableColumns->first();

        DB::table($tableName)
            ->whereNull($targetColumn)
            ->update([$targetColumn => DB::raw($expression)]);
    }
};
