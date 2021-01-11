<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        - id (auto incremented id) [OK]
        - email (string with max length of 255 characters - UK) [OK]
        - name (string with max length of 255 characters) [OK]
        - password (min 8 characters, min 1 capital letter, 1 number) [OK]
        - date_of_birth (Date in format YYYY-MM-DD) [OK]

        - active (bool) [OK]
        - superuser (bool, default false)
        */
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 255)->unique();
            $table->string('name', 255);
            $table->string('password', 100);
            $table->date('date_of_birth');
            $table->boolean('active')->default(true);
            $table->boolean('superuser')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
