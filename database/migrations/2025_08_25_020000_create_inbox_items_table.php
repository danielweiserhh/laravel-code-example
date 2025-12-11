<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('source')->default('manual'); 
            $table->boolean('is_processed')->default(false);
            $table->foreignId('converted_to_card_id')->nullable()->constrained('cards')->onDelete('set null');
            $table->jsonb('ai_suggestions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_items');
    }
};
