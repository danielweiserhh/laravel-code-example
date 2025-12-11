<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::table('focus_sessions', function (Blueprint $table) {
            $table->index('status', 'focus_sessions_status_idx');
            $table->index('started_at', 'focus_sessions_started_at_idx');
            $table->index('ended_at', 'focus_sessions_ended_at_idx');
            $table->index(['user_id', 'status'], 'focus_sessions_user_status_idx');
            $table->index(['user_id', 'started_at'], 'focus_sessions_user_started_idx');
        });

        
        Schema::table('routines', function (Blueprint $table) {
            $table->index('is_active', 'routines_is_active_idx');
            $table->index(['user_id', 'is_active'], 'routines_user_active_idx');
        });

        
        Schema::table('routine_steps', function (Blueprint $table) {
            $table->index(['routine_id', 'position'], 'routine_steps_routine_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('focus_sessions', function (Blueprint $table) {
            $table->dropIndex('focus_sessions_status_idx');
            $table->dropIndex('focus_sessions_started_at_idx');
            $table->dropIndex('focus_sessions_ended_at_idx');
            $table->dropIndex('focus_sessions_user_status_idx');
            $table->dropIndex('focus_sessions_user_started_idx');
        });

        Schema::table('routines', function (Blueprint $table) {
            $table->dropIndex('routines_is_active_idx');
            $table->dropIndex('routines_user_active_idx');
        });

        Schema::table('routine_steps', function (Blueprint $table) {
            $table->dropIndex('routine_steps_routine_position_idx');
        });
    }
};
