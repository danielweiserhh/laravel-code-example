<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->index('list_id', 'cards_list_id_idx');
            $table->index(['list_id', 'position'], 'cards_list_position_idx');
            $table->index('created_at', 'cards_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex('cards_list_id_idx');
            $table->dropIndex('cards_list_position_idx');
            $table->dropIndex('cards_created_at_idx');
        });
    }
};
