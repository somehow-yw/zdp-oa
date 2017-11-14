<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDpGoodsAppraisesAddHasImg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('main_data.connection', 'mysql_zdp_main'))
            ->table('dp_goods_appraises', function ($table) {
                $table->char('hasImg',1)->default('0')->comment('评论是否有图片');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('main_data.connection', 'mysql_zdp_main'))
            ->table('dp_goods_appraises',function($table){
               $table->dropColumn('hasImg');
            });
    }
}
