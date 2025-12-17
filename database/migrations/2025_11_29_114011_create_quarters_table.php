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
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. "1st Quarter", "2nd Quarter"
            $table->text('description')->nullable();     // Optional description
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');                  // Quarter belongs to a semester
            $table->boolean('is_active')->default(false); // Active status flag
            $table->timestamps();
            $table->softDeletes();                       // Soft delete support
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarters');
    }
};
