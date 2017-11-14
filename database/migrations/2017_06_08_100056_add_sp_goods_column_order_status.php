<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpGoodsColumnOrderStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_bi')->table('sp_goods', function (Blueprint $table){
            $table->tinyInteger('order_status')
                  ->after('order_id')
                  ->index()
                  ->common('订单当前状态');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_bi')->table('sp_goods', function (Blueprint $table){
            $table->dropColumn('order_status');
        });
    }
}
