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
        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->id();
            
            // Assignment this attachment belongs to
            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->onDelete('cascade');
            
            // File information
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // MIME type
            $table->unsignedBigInteger('file_size')->nullable(); // Size in bytes
            $table->string('original_name')->nullable(); // Original filename
            
            // File metadata
            $table->json('metadata')->nullable(); // Additional file info (dimensions, etc.)
            
            // Display order
            $table->unsignedTinyInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('assignment_id');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_attachments');
    }
};

