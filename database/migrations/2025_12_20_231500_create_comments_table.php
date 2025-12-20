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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship - comment can belong to any model
            $table->morphs('commentable'); // Creates commentable_type and commentable_id
            
            // User who created the comment
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Comment content
            $table->text('body');
            
            // Parent comment (for replies) - nullable for top-level comments
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->onDelete('cascade');
            
            // Status
            $table->enum('status', ['published', 'pending', 'deleted', 'spam'])
                ->default('published');
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // IP address and user agent for moderation
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            // Note: morphs() already creates an index on commentable_type and commentable_id
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
