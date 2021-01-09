<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            /*
            - id (auto incremented id) [OK]
            - title (string with max length of 255 characters) [OK]
            - isbn (10 digits, select from an array of valid isbn provided below) [OK]
            - published_at (Date in format YYYY-MM-DD) [OK]
            - status (enum [‘CHECKED_OUT’,’AVAILABLE’]) [OK]
            */

            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->string('isbn', 10)->unique();
            $table->date('published_at');
            $table->enum('status', ['CHECKED_OUT', 'AVAILABLE'])->default('AVAILABLE');
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
