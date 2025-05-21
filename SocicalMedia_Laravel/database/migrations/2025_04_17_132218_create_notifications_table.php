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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Người nhận thông báo
            $table->string('type')->nullable(); // Loại thông báo: comment, post_update, etc.
            $table->text('notification_content'); // Nội dung thông báo
            $table->unsignedBigInteger('notifiable_id')->nullable(); // ID đối tượng liên quan (Post, Comment, ...)
            $table->string('notifiable_type')->nullable(); // Loại đối tượng liên quan (Model class name)
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
