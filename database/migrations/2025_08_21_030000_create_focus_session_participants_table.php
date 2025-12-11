<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focus_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('focus_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['focus_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focus_session_participants');
    }
};
