<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCountyDpPianqu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_zdp_main')->table('dp_pianqu',function (Blueprint $table){
            $table->smallInteger('county')->after('city')->default(0)->common('区县id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_zdp_main')->table('dp_pianqu',function (Blueprint $table){
            $table->dropColumn('county');
        });
    }
}
