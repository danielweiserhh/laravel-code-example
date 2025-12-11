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
            $table->index(['due_date', 'is_completed'], 'cards_due_date_is_completed_index');
        });

        
        
        Schema::table('routine_executions', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'routine_executions_user_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex('cards_due_date_is_completed_index');
        });

        Schema::table('routine_executions', function (Blueprint $table) {
            $table->dropIndex('routine_executions_user_date_index');
        });
    }
};
