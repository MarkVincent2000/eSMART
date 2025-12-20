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
        Schema::create('comment_replies', function (Blueprint $table) {
            $table->id();
            
            // Parent comment (the comment being replied to)
            $table->foreignId('comment_id')
                ->constrained('comments')
                ->onDelete('cascade');
            
            // Reply comment (the reply itself)
            $table->foreignId('reply_id')
                ->constrained('comments')
                ->onDelete('cascade');
            
            // Depth level for nested replies (0 = direct reply, 1 = reply to reply, etc.)
            $table->unsignedTinyInteger('depth')->default(0);
            
            $table->timestamps();
            
            // Ensure a comment can't reply to itself
            $table->unique(['comment_id', 'reply_id']);
            
            // Indexes
            $table->index('comment_id');
            $table->index('reply_id');
            $table->index('depth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_replies');
    }
};
