<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_action_logs', function (Blueprint $table) {
            /*
            - id (auto incremented id) [OK]
            - book_id (integer) [OK]
            - user_id (integer) [OK]
            - action (enum [‘CHECKIN’, ‘CHECKOUT’]) [OK]
            - created_at (timestamp) [OK]
            */

            $table->bigIncrements('id');
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('action', ['CHECKIN', 'CHECKOUT']);
            $table->timestamp('created_at');

            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_action_logs');
    }
}
