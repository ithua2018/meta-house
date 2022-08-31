<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('用户ID');
            $table->string('username', 50)->comment('用户名');
            $table->string('password', 100)->comment('密码');
            $table->char('mobile',11)->comment('用户手机号');
            $table->enum('sex', [0, 1, 2])->default(0)->comment('0-未知 1-男 2-女');
            $table->string('avatar', 200)->default('')->comment('用户头像');
            $table->enum('role',[1,2,3])->comment('角色类型 1-房东 2-租客 3-购房');
            $table->timestamp('register_time')->comment('注册时间');
            $table->timestamp('last_login_time')->comment('上次登录时间');
            $table->ipAddress('last_login_ip')->comment('上次登录IP');
            $table->timestamp('login_time')->comment('当前登录时间');
            $table->ipAddress('login_ip')->comment('当前登录IP');
            $table->timestamp('update_time')->comment('更新时间');
            $table->enum('status',[0, 1])->default(0)->comment('是否禁用 0-正常 1-禁用');
            $table->softDeletes();
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
