<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsAppraisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->create('goods_appraises', function (Blueprint $table) {
                $table->unsignedInteger('id')->comment('评价ID')->primary();

                $table->unsignedInteger('order_id')->comment('订单ID')->index();
                $table->unsignedInteger('buyer_id')->comment('买家商户ID')->index();
                $table->unsignedInteger('buyer_shop_id')->comment('买家店铺ID')->index();
                $table->unsignedInteger('seller_shop_id')->comment('卖家店铺ID')->index();
                $table->unsignedInteger('goods_id')->comment('商品ID')->index();
                $table->tinyInteger('good_appraise')->comment('好评为1，非好评为0，方便统计')->index();
                $table->tinyInteger('has_img')->comment('是否有图，1有图，0无图')->index();
                $table->smallInteger('quality')->comment('评价等级')->index();

                $table->timestamp('created_at')->comment('下单时间')->index();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('bi.connection'))->drop('goods_appraises');
    }
}
