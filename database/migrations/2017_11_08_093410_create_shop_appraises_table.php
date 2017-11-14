<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopAppraisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->create('shop_appraises', function (Blueprint $table) {
                $table->unsignedInteger('id')->comment('订单ID')->index();

                $table->unsignedInteger('buyer_id')->index()->comment('买家商户ID');
                $table->unsignedInteger('sell_shop_id')->comment('卖家店铺ID')->index();
                $table->decimal('money',11,2)->index()->comment('订单价格');
                $table->unsignedInteger('service_appraise_id')->index()->comment('服务评价ID')->nullable();
                $table->tinyInteger('delivery')->comment('物流评价')->index()->nullable();
                $table->tinyInteger('sell_service')->comment('服务评价')->index()->nullable();

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
        Schema::connection(config('bi.connection'))->drop('shop_appraises');
    }
}
