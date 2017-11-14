<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopByStatistics extends Migration
{
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->create('shop',function(Blueprint $table){
                $table->increments('shop_id')->comment('店铺id');
                $table->string('shop_name')->index()->comment('店铺名称');
                $table->tinyInteger('shop_type')->index()->comment('店铺类型：1=>供应商,0=>采购商');
                $table->string('province')->index()->comment('省');
                $table->string('city')->index()->comment('市');
                $table->string('country')->index()->comment('区');
                $table->timestamp('register_time')->index()->comment('注册时间');
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
            ->drop('shop');
    }
}
