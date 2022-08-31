<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubwayStationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subway_station', function (Blueprint $table) {
            $table->id();
            $table->integer('station_id')->comment('站点ID');
            $table->string('address')->comment('地铁');
            $table->string('pname')->comment('省份');
            $table->string('cityname')->comment('城市');
            $table->string('adname')->comment('区');
            $table->integer('city_id')->comment('市id');
            $table->string('name')->comment('站名全称');
            $table->float('lon')->comment('经度');
            $table->float('lat')->comment('维度');
            $table->string('line')->comment('几号线');
          //  $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subway_station');
    }
}
