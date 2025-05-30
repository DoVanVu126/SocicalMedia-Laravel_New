<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
   Schema::create('reactions', function (Blueprint $table) {
            $table->increments('id'); // id: AUTO_INCREMENT
            $table->integer('user_id'); // user_id: int(11)
            $table->integer('post_id'); // post_id: int(11)
            $table->string('type', 255); // type: varchar(255)
            $table->timestamp('created_at')->useCurrent()->useCurrentOnUpdate(); // created_at: current_timestamp() ON UPDATE
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // updated_at: current_timestamp() ON UPDATE
        });
}


    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
