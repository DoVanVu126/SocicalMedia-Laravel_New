<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Liên kết với bảng users
            $table->text('content'); // Nội dung bài viết
            $table->string('imageurl')->nullable(); // Đường dẫn ảnh (nullable nếu không có ảnh)
            $table->string('videourl')->nullable(); // Đường dẫn video (nullable nếu không có video)
            $table->string('status')->default('active'); // Trạng thái bài viết (active hoặc inactive)
            $table->timestamps(); // Tự động thêm created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
