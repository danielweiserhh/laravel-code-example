<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inbox_items', function (Blueprint $table) {
            $table->index('user_id', 'inbox_user_id_idx');
            $table->index('workspace_id', 'inbox_workspace_id_idx');
            $table->index('created_at', 'inbox_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('inbox_items', function (Blueprint $table) {
            $table->dropIndex('inbox_user_id_idx');
            $table->dropIndex('inbox_workspace_id_idx');
            $table->dropIndex('inbox_created_at_idx');
        });
    }
};
