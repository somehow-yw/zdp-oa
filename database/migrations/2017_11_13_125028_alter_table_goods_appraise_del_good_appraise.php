<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableGoodsAppraiseDelGoodAppraise extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->table('goods_appraises',function(Blueprint $table){
                $table->dropColumn('good_appraise');
                $table->dropColumn('has_img');
                $table->dropColumn('quality');
                $table->dropColumn('medium_appraise');
                $table->dropColumn('poor_appraise');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('bi.connection'))
            ->table('goods_appraises',function(Blueprint $table){
                $table->tinyInteger('good_appraise')->index();
                $table->tinyInteger('has_img')->index();
                $table->smallInteger('quality')->index();
                $table->tinyInteger('medium_appraise')->index();
                $table->tinyInteger('poor_appraise')->index();
            });
    }
}
