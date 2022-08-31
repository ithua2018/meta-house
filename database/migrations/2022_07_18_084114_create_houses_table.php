<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->char('uuid',32)->comment('UUID');
            $table->tinyInteger('type')->comment('类型 1-出售 2-整租 3-合租');
            $table->float('area')->comment('房屋面积');
            $table->tinyInteger('floor')->comment('层数');
            $table->tinyInteger('is_elevator')->comment('是否有电梯 0-无，1-有');
            $table->decimal('price_range_min')->comment('最小价格');
            $table->decimal('price_range_max')->comment('最大价格');
            $table->integer('vacancy_time')->comment('房屋空出时间');
            $table->tinyInteger('halls')->comment('几厅');
            $table->tinyInteger('rooms')->comment('几室');
            $table->string('facilities')->comment('房屋设施 格式1,2,3,4');
            $table->decimal('lon',10, 7)->comment('经度');
            $table->decimal('lat', 10, 7)->comment('维度');
            $table->string('address')->comment('详细地址');
            $table->text('images')->comment('图片地址,img1|img2');
            $table->text('content')->comment('介绍');
            $table->text('roommate')->comment('室友 json数组[{num:1,sex:女,month:12}]');
            $table->tinyInteger('status')->default(0)->comment('状态 0-正常 1-已出租或者已售出');
            $table->integer('views')->default(0)->comment('浏览次数');
            $table->integer('add_time')->comment('发布时间');
            $table->integer('update_time')->comment('更新时间');
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
        Schema::dropIfExists('houses');
    }
}
