<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopAppraiseInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql_zdp_main')
            ->create('dp_shop_appraise_info', function ($table) {
                $table->increments('id');
                $table->integer('shopid')->comment('对应店铺id');
                $table->integer('appraise_num')->nullable()->comment('店铺总评论');
                $table->integer('good_appraise_num')->nullable()->comment('店铺好评数');
                $table->double('good_appraise_ratio')->nullable()->comment('店铺好评率');
                $table->unique('shopid');
            });

        \App\Models\DpShopInfo::query()
            ->leftjoin('dp_goods_appraises as g', 'dp_shopInfo.shopId', '=', 'g.sell_shop_id')
            ->leftjoin('dp_goods_appraises as gd', function ($join) {
                $join->on('dp_shopInfo.shopId', '=', 'gd.sell_shop_id')
                    ->where('gd.quality', '=', \App\Models\DpGoodsAppraises::FIVE);
            })
            ->groupBy('dp_shopInfo.shopId')
            ->select(
                'dp_shopInfo.shopId as shopid',
                DB::raw(
                    'count(g.sell_shop_id) as appraise_num'
                ),
                DB::raw(
                    'count(gd.sell_shop_id) as good_appraise_num'
                ),
                DB::raw(
                    'count(gd.sell_shop_id)/count(g.sell_shop_id) as good_appraise_ratio'
                )
            )->chunk('100', function ($shopInfo) {
                foreach ($shopInfo->toArray() as $shopAppraise) {
                    try {
                        \App\Models\DpShopAppraiseInfo::create($shopAppraise);
                    } catch (Exception $e) {
                        echo $shopAppraise . "\n";
                    }

                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::connection('mysql_zdp_main')->drop('shop_appraise_info');
    }
}
