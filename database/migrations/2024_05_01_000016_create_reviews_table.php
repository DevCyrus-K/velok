<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews')) {
            return;
        }

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewer_name');
            $table->string('reviewer_role');
            $table->decimal('rating', 2, 1);
            $table->text('review_message');
            $table->string('photo_path');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->boolean('featured')->default(false);
            $table->text('moderation_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('source_page')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index('status');
            $table->index('rating');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
