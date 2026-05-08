<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_applications')) {
            return;
        }

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_job_id')
                ->nullable()
                ->constrained('career_jobs')
                ->nullOnDelete();
            $table->string('job_title');
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone');
            $table->string('current_location')->nullable();
            $table->string('resume_url')->nullable();
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['new', 'reviewing', 'shortlisted', 'rejected', 'hired'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->string('source_page')->nullable();
            $table->timestamps();

            $table->index('career_job_id');
            $table->index('status');
            $table->index('email');
            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
