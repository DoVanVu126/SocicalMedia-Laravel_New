<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationUserTable extends Migration
{
    public function up()
    {
        Schema::create('notification_user', function (Blueprint $table) {
            $table->increments('id'); // id: AUTO_INCREMENT
            $table->integer('notification_id'); // notification_id: int(11)
            $table->integer('user_id'); // user_id: int(11)
            $table->tinyInteger('is_read')->nullable()->default(0); // is_read: tinyint(1) default 0
            $table->tinyInteger('is_deleted')->nullable()->default(0); // is_deleted: tinyint(1) default 0
            $table->timestamp('created_at')->nullable(); // created_at: nullable timestamp
            $table->timestamp('updated_at')->nullable(); // updated_at: nullable timestamp
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_user');
    }
}
