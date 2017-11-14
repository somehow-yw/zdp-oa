<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CallLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
              ->create('call_log', function (Blueprint $table) {
                  $table->increments('id');

                  $table->integer('buyer_id')->unsigned()->index();
                  $table->string('buyer_type', 16)->index();
                  $table->string('buyer_name', 32)->index();
                  $table->string('buyer_province', 32)->index();
                  $table->string('buyer_city', 32)->index();
                  $table->string('buyer_district', 32)->index();
                  $table->string('buyer_market', 32)->index();

                  $table->integer('seller_id')->unsigned()->index();
                  $table->string('seller_type', 16)->index();
                  $table->string('seller_name', 32)->index();
                  $table->string('seller_province', 32)->index();
                  $table->string('seller_city', 32)->index();
                  $table->string('seller_district', 32)->index();
                  $table->string('seller_market', 32)->index();

                  $table->integer('goods_id')->unsigned()->index();
                  $table->string('goods_name', 32)->index();
                  $table->string('goods_sort', 32)->index();
                  $table->string('goods_type_node', 32)->index();
                  $table->string('goods_brand', 32)->index();
                  $table->decimal('goods_price', 11, 2)->unsigned()->index();

                  $table->integer('call_times')->default(0);

                  $table->date('call_date');
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
              ->drop('call_log');
    }
}
