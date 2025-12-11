<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->index(['list_id', 'position']);
            $table->index(['board_id', 'is_completed']);
            $table->index('due_date');
        });

        Schema::table('boards', function (Blueprint $table) {
            $table->index(['workspace_id', 'is_favorite']);
        });

        Schema::table('lists', function (Blueprint $table) {
            $table->index(['board_id', 'position']);
        });

        Schema::table('inbox_items', function (Blueprint $table) {
            $table->index(['user_id', 'is_processed', 'created_at']);
        });

        Schema::table('routines', function (Blueprint $table) {
            $table->index(['user_id', 'is_active']);
        });

        Schema::table('focus_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['card_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex(['list_id', 'position']);
            $table->dropIndex(['board_id', 'is_completed']);
            $table->dropIndex(['due_date']);
        });

        Schema::table('boards', function (Blueprint $table) {
            $table->dropIndex(['workspace_id', 'is_favorite']);
        });

        Schema::table('lists', function (Blueprint $table) {
            $table->dropIndex(['board_id', 'position']);
        });

        Schema::table('inbox_items', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_processed', 'created_at']);
        });

        Schema::table('routines', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
        });

        Schema::table('focus_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['card_id', 'created_at']);
        });
    }
};

