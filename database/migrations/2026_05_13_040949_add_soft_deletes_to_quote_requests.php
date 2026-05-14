<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('quote_requests') || Schema::hasColumn('quote_requests', 'deleted_at')) {
            return;
        }

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('quote_requests') || ! Schema::hasColumn('quote_requests', 'deleted_at')) {
            return;
        }

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
