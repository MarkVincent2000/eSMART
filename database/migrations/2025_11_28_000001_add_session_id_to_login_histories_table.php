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
        if (!Schema::hasColumn('login_histories', 'session_id')) {
            Schema::table('login_histories', function (Blueprint $table) {
                $table->string('session_id', 191)->nullable()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('login_histories', 'session_id')) {
            Schema::table('login_histories', function (Blueprint $table) {
                $table->dropColumn('session_id');
            });
        }
    }
};

