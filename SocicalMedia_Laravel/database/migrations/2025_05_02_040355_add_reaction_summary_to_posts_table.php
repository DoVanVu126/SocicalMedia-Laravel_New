<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReactionSummaryToPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('reaction_summary')->nullable()->after('status'); // Thêm cột reaction_summary kiểu JSON
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('reaction_summary'); // Xóa cột nếu rollback
        });
    }
}
