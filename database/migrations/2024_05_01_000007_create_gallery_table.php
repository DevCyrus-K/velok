<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gallery')) {
            return;
        }

        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url');
            $table->string('thumbnail_url')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->boolean('featured')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery');
    }
};
