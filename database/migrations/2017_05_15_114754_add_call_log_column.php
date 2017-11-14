<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallLogColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
              ->table('call_log', function (Blueprint $table) {
                  $table->bigInteger('telnumber')
                        ->after('buyer_market')
                        ->common('拨打手机号码');
                  $table->string('buyer_shop')
                        ->after('buyer_name')
                        ->index()
                        ->common('买家店铺名字');
                  $table->string('goods_title')
                        ->after('goods_name')
                        ->common('商品标题');
              });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
              ->table('call_log', function (Blueprint $table) {
                  $table->dropColumn('telnumber');
                  $table->dropColumn('buyer_shop');
                  $table->dropColumn('goods_title');
              });
    }
}
