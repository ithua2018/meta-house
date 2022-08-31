<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSomeFieldsDefaultValueToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->integer('last_login_time')->default(0)->change();
            $table->string('last_login_ip')->default('')->change();
            $table->string('password', 100)->nullable()->change();
           // $table->enum('current_role',['0','1','2','3'])->comment('角色类型 1-房东 2-租客 3-购房 0-未选择')->change();
         //   DB::statement("ALTER TABLE users CHANGE COLUMN current_role  TINYINT UNSIGNED NOT NULL");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
