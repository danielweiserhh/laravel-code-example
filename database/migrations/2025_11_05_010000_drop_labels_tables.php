<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        if (Schema::hasTable('card_labels')) {
            Schema::dropIfExists('card_labels');
        }

        
        if (Schema::hasTable('labels')) {
            Schema::dropIfExists('labels');
        }
    }

    public function down(): void
    {
        
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color');
            $table->timestamps();
        });

        
        Schema::create('card_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('label_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['card_id', 'label_id']);
        });
    }
};
