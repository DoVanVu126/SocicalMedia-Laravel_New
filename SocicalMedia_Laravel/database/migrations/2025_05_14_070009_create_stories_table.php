<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('imageurl')->nullable();
            $table->string('videourl')->nullable();
            $table->text('content')->nullable(); // nếu có text
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->timestamp('expires_at')->nullable(); // dùng cho tự hủy sau 24h
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
