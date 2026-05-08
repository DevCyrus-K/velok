<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gallery')) {
            return;
        }

        Schema::table('gallery', function (Blueprint $table) {
            if (!Schema::hasColumn('gallery', 'description')) {
                $table->text('description')->nullable()->after('alt_text');
            }

            if (!Schema::hasColumn('gallery', 'order')) {
                $table->integer('order')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('gallery')) {
            return;
        }

        Schema::table('gallery', function (Blueprint $table) {
            if (Schema::hasColumn('gallery', 'order')) {
                $table->dropColumn('order');
            }

            if (Schema::hasColumn('gallery', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
