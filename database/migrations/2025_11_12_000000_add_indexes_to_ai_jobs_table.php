<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_jobs', function (Blueprint $table) {
            $table->index('user_id', 'ai_jobs_user_id_idx');
            $table->index('workspace_id', 'ai_jobs_workspace_id_idx');
            $table->index('type', 'ai_jobs_type_idx');
            $table->index('status', 'ai_jobs_status_idx');
            $table->index('created_at', 'ai_jobs_created_at_idx');
            $table->index(['workspace_id', 'status', 'created_at'], 'ai_jobs_ws_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ai_jobs', function (Blueprint $table) {
            $table->dropIndex('ai_jobs_user_id_idx');
            $table->dropIndex('ai_jobs_workspace_id_idx');
            $table->dropIndex('ai_jobs_type_idx');
            $table->dropIndex('ai_jobs_status_idx');
            $table->dropIndex('ai_jobs_created_at_idx');
            $table->dropIndex('ai_jobs_ws_status_created_idx');
        });
    }
};
