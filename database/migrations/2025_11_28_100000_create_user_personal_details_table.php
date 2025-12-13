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
        Schema::create('user_personal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('guardian_first_name')->nullable();
            $table->string('guardian_last_name')->nullable();
            $table->string('guardian_middle_name')->nullable();
            $table->string('guardian_suffix')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->string('guardian_contact_no')->nullable();

            $table->string('sex')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('religion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personal_details');
    }
};










