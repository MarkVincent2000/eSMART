<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship - can be linked to any model (Event, Assignment, etc.)
            $table->morphs('notifiable');
            
            // Recipient information
            $table->string('phone_number'); // The phone number that received the SMS
            $table->string('recipient_type')->nullable(); // 'student' or 'guardian'
            $table->unsignedBigInteger('user_id')->nullable(); // Link to user if applicable
            
            // Message details
            $table->text('message'); // The SMS message content
            $table->string('sender_name')->nullable(); // The sender name used
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'undelivered'])->default('pending');
            $table->text('error_message')->nullable(); // Error message if failed
            
            // API response data
            $table->json('api_response')->nullable(); // Store full API response for debugging
            $table->string('message_id')->nullable(); // External message ID from SMS provider
            
            // Metadata
            $table->timestamp('sent_at')->nullable(); // When the SMS was actually sent
            $table->timestamp('delivered_at')->nullable(); // When delivery was confirmed
            $table->timestamps();
            
            // Indexes (morphs() already creates index for notifiable_type and notifiable_id)
            $table->index('user_id');
            $table->index('phone_number');
            $table->index('status');
            $table->index('sent_at');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_notifications');
    }
};
