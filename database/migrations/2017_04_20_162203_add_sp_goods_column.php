<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpGoodsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_bi')->table('sp_goods', function (Blueprint $table) {
            $table->tinyInteger('method', false, true)
                  ->after('goods_price')
                  ->index()
                  ->comment('客户下单方式');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_bi')->table('sp_goods', function (Blueprint $table) {
            $table->dropColumn('method');
        });
    }
}
