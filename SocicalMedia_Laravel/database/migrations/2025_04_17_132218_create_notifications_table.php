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
            $table->increments('id'); // id: AUTO_INCREMENT
            $table->integer('user_id'); // user_id: int(11)
            $table->text('notification_content'); // notification_content: text
            $table->integer('notifiable_id'); // notifiable_id: int(11)
            $table->string('notifiable_type')->nullable(); // notifiable_type: varchar(255), NULL
            $table->tinyInteger('is_read')->nullable()->default(0); // is_read: tinyint(1), default 0
            $table->longText('data')->nullable(); // data: longtext, NULL
            $table->timestamp('created_at')->useCurrent(); // created_at: current_timestamp()
            $table->dateTime('updated_at')->nullable(); // updated_at: datetime, NULL
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
