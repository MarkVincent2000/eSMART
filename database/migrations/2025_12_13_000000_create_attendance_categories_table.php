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
        Schema::create('attendance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6366f1'); // Hex color code
            $table->string('icon')->nullable(); // Icon class or name (e.g., 'mdi-school', 'fas fa-chalkboard')
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('slug');
            $table->index('is_active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_categories');
    }
};
