<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('remember_token');
            $table->string('otp_code', 512)->nullable()->after('two_factor_enabled');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->unsignedTinyInteger('otp_attempts')->default(0)->after('otp_expires_at');
            $table->timestamp('last_login_at')->nullable()->after('otp_attempts');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'otp_code',
                'otp_expires_at',
                'otp_attempts',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
