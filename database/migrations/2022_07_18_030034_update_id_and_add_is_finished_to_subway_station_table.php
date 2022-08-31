<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIdAndAddIsFinishedToSubwayStationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subway_station', function (Blueprint $table) {
          //  $table->string('id')->primary()->comment('ID')->change();
            $table->integer('is_finished')->comment( '是否完成 0在建 1完成');
            $table->dropColumn('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subway_station', function (Blueprint $table) {
            //
        });
    }
}
