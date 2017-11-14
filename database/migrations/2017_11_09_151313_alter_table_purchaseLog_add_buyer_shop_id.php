<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePurchaseLogAddBuyerShopId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->table('purchase_log',function(Blueprint $table){
               $table->unsignedInteger('buyer_shop_id')->index()->comment('买家店铺id');
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
            ->table('purchase_log',function(Blueprint $table){
                $table->dropColumn('buyer_shop_id');
            });
    }
}
