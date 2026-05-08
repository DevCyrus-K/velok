<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email_verified_at');
            $table->string('job_title')->nullable()->after('phone');
            $table->string('company')->nullable()->after('job_title');
            $table->string('location')->nullable()->after('company');
            $table->string('avatar_path')->nullable()->after('location');
            $table->text('bio')->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'job_title',
                'company',
                'location',
                'avatar_path',
                'bio',
            ]);
        });
    }
};
