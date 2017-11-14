<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCallLogColumnBuyerMarket extends Migration
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
                  $table->dropColumn('buyer_market');
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
                  $table->string('buyer_market', 32)
                        ->index()
                        ->after('buyer_district')
                        ->common('买家市场');
              });
    }
}
