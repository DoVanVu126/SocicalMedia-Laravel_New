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
            $table->id(); // id: UNSIGNED AUTO_INCREMENT
            $table->unsignedBigInteger('user_id'); // user_id: int(11)
            $table->string('imageurl', 255)->nullable(); // imageurl: varchar(255)
            $table->string('videourl', 255)->nullable(); // videourl: varchar(255)
            $table->text('content')->nullable(); // content: text
            $table->enum('visibility', ['public', 'private'])->default('public'); // visibility: enum
            $table->timestamp('expires_at')->nullable(); // expires_at: timestamp NULL
            $table->timestamp('created_at')->useCurrent(); // created_at: current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // updated_at: current_timestamp() ON UPDATE
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
