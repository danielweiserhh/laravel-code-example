<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('user_daily_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('plan_date');
            $table->json('big_three')->nullable();
            $table->text('note_for_user')->nullable();
            $table->foreignUuid('ai_job_id')->nullable()->constrained('ai_jobs')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'plan_date']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('user_daily_plans');
    }
};
