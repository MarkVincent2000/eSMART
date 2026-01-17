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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Unique identifier (e.g., 'site.logo')
            $table->string('key')->unique();
            
            // Human-readable name (e.g., 'Site Logo')
            $table->string('name');
            
            // Setting value
            $table->text('value')->nullable();
            
            // Type of setting (e.g., 'text', 'number', 'boolean', 'file', etc.)
            $table->string('type')->default('text');
            
            // Group for organizing settings
            $table->string('group')->nullable();
            
            // Whether the setting is locked from modification
            $table->boolean('is_locked')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('key');
            $table->index('group');
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
