<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_information', function (Blueprint $table) {
            $table->id()->comment('用户信息ID');
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->enum('role',[1,2,3])->comment('角色类型 1-房东 2-租客 3-购房');
            $table->char('uuid',32)->unique()->comment('MD5(user_id_role))');
            $table->string('nick_name', 20)->default('')->comment('昵称');
            $table->string('true_name',20)->default('')->comment('真实姓名');
            $table->enum('sex', [0, 1, 2])->default(0)->comment('0-未知 1-男 2-女');
            $table->string('avatar', 200)->default('')->comment('用户头像');
            $table->string('label',200)->default('')->comment('用户标签');
            $table->integer('add_time')->comment('添加时间');
            $table->integer('update_time')->default(0)->comment('更新时间');
            $table->softDeletes();
        });
//        Schema::table('users_information', static function(Blueprint $table):void {
//            $table->foreign('user_id')->references('id')->on('users');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_information');
    }
}
