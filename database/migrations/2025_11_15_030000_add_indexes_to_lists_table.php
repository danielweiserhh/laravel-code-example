<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->index('board_id', 'lists_board_id_idx');
            $table->index(['board_id', 'position'], 'lists_board_position_idx');
            $table->index('is_archived', 'lists_is_archived_idx');
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->dropIndex('lists_board_id_idx');
            $table->dropIndex('lists_board_position_idx');
            $table->dropIndex('lists_is_archived_idx');
        });
    }
};
