<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableGoodsAppraiseAddGoodsNameAddTimePrince extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->table('goods_appraises', function (Blueprint $table) {
                $table->string('goods_name', 64)->comment('商品名称')->index();
                $table->timestamp('add_time')->comment('上架时间')->index();
                $table->decimal('money', 11, 2)->comment('商品总价')->comment()->index();
                $table->integer('goods_num')->comment('商品数量')->index();
                $table->tinyInteger('appraise')->comment('是否被评价：1=>被评价，0=>未评价')->index();
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
            ->table('goods_appraises', function (Blueprint $table) {
                $table->dropColumn('goods_name');
                $table->dropColumn('add_time');
                $table->dropColumn('money');
                $table->dropColumn('goods_num');
                $table->dropColumn('appraise');
            });
    }
}
