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
        DB::transaction(function(){
            Schema::create('user_action_logs', function (Blueprint $table) {
                /*
                - id (auto incremented id) [OK]
                - book_id (integer) [OK]
                - user_id (integer) [OK]
                - action (enum ['CHECKIN', 'CHECKOUT']) [OK]
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

            DB::unprepared('
                CREATE OR REPLACE FUNCTION fnc_trig_update_book_status()
                RETURNS trigger AS $fnc_trig_update_book_status$
                DECLARE
                    s_new_status TEXT;
                BEGIN
                    -- check action to confirm book status
                    IF NEW.action = \'CHECKOUT\' THEN
                        s_new_status = \'CHECKED_OUT\';
                    ELSE
                        s_new_status = \'AVAILABLE\';
                    END IF;
                    
                    UPDATE books
                    SET status = s_new_status
                    WHERE id = NEW.book_id
                    AND status <> s_new_status;
                
                    RETURN NEW;
                END;
                $fnc_trig_update_book_status$ LANGUAGE plpgsql;
                
                DROP TRIGGER IF EXISTS trig_update_book_status ON user_action_logs;
                CREATE TRIGGER trig_update_book_status
                    AFTER INSERT ON user_action_logs
                    FOR EACH ROW
                    EXECUTE PROCEDURE fnc_trig_update_book_status();
            ');
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
        DB::unprepared('
            DROP TRIGGER IF EXISTS trig_update_book_status ON user_action_logs;
            DROP FUNCTION IF EXISTS fnc_trig_update_book_status;
        ');
    }
}
