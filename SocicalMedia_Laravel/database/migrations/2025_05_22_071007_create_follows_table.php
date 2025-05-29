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
      Schema::create('follows', function (Blueprint $table) {
            $table->increments('id'); // id: AUTO_INCREMENT, primary key
            $table->integer('follower_id'); // follower_id: int(11), not null
            $table->integer('followed_id'); // followed_id: int(11), not null
            $table->timestamp('created_at')->nullable(); // created_at: timestamp, NULL
            $table->timestamp('updated_at')->nullable(); // updated_at: timestamp, NULL

            // Nếu muốn index:
            $table->index('follower_id');
            $table->index('followed_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
