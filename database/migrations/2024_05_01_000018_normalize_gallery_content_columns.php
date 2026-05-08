<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gallery')) {
            return;
        }

        Schema::table('gallery', function (Blueprint $table) {
            if (!Schema::hasColumn('gallery', 'image_path')) {
                $table->string('image_path')->nullable()->after('title');
            }

            if (!Schema::hasColumn('gallery', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('category');
            }
        });

        if (Schema::hasColumn('gallery', 'image_url')) {
            DB::table('gallery')
                ->whereNull('image_path')
                ->update(['image_path' => DB::raw('image_url')]);
        }

        DB::table('gallery')
            ->whereNull('alt_text')
            ->update(['alt_text' => DB::raw('title')]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('gallery')) {
            return;
        }

        Schema::table('gallery', function (Blueprint $table) {
            if (Schema::hasColumn('gallery', 'alt_text')) {
                $table->dropColumn('alt_text');
            }

            if (Schema::hasColumn('gallery', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
