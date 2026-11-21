<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lesson_id', 'created_at']);
        });

        Schema::create('replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['thread_id', 'created_at']);
        });

        Schema::create('ruka_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('role', 10);
            $table->text('content');
            $table->timestamps();

            $table->index(['user_id', 'lesson_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruka_messages');
        Schema::dropIfExists('replies');
        Schema::dropIfExists('threads');
    }
};