<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('user_daily_plans', function (Blueprint $table) {
            if (Schema::hasColumn('user_daily_plans', 'quick_wins')) {
                $table->dropColumn('quick_wins');
            }
        });
    }

    
    public function down(): void
    {
        Schema::table('user_daily_plans', function (Blueprint $table) {
            $table->json('quick_wins')->nullable()->after('big_three');
        });
    }
};
