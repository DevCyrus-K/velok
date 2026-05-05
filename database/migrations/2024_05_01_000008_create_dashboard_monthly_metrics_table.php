<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dashboard_monthly_metrics')) {
            return;
        }

        Schema::create('dashboard_monthly_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->unsignedInteger('completed_moves')->default(0);
            $table->unsignedInteger('cancelled_bookings')->default(0);
            $table->unsignedInteger('desktop_visitors')->default(0);
            $table->unsignedInteger('mobile_visitors')->default(0);
            $table->unsignedInteger('tablet_visitors')->default(0);
            $table->timestamps();

            $table->unique('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_monthly_metrics');
    }
};
