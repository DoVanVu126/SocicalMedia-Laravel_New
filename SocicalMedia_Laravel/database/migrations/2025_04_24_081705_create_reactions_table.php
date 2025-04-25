<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('reactions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('post_id');
        $table->string('type'); // like, haha, wow, sad, angry
        $table->timestamps();

        $table->unique(['user_id', 'post_id']); // mỗi người 1 cảm xúc/post
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
    });
}


    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
