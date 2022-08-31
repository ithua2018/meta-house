<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToHouses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->string('country')->after('address')->comment('国家');
            $table->string('province')->after('address')->comment('省份');
            $table->string('city')->after('address')->comment('城市');
            $table->string('district')->nullable()->after('address')->comment('区');
            $table->string('township')->nullable()->after('address')->comment('街道');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('houses', function (Blueprint $table) {
            //
        });
    }
}
