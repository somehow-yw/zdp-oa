<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PurchaseLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
              ->create('purchase_log', function (Blueprint $table) {
                  $table->integer('id')->unsigned()->primary();

                  $table->integer('buyer_id')->unsigned()->index();
                  $table->string('buyer_type', 16)->index();
                  $table->string('buyer_province', 32)->index();
                  $table->string('buyer_city', 32)->index();
                  $table->string('buyer_district', 32)->index();
                  $table->string('buyer_market', 32)->index();

                  $table->integer('seller_id')->unsigned()->index();
                  $table->string('seller_type', 16)->index();
                  $table->string('seller_province', 32)->index();
                  $table->string('seller_city', 32)->index();
                  $table->string('seller_district', 32)->index();
                  $table->string('seller_market', 32)->index();

                  $table->integer('goods_id')->unsigned()->index();
                  $table->string('goods_type_node', 32)->index();
                  $table->string('goods_brand', 32)->index();
                  $table->decimal('goods_price', 11, 2)->unsigned()->index();

                  $table->integer('num')->unsigned()->index();
                  $table->decimal('price', 11, 2)->unsigned()->index();

                  $table->string('pay_method', 16)->index();
                  $table->string('pay_online_channel', 16)->index();

                  $table->string('delivery_method', 16)->index();

                  $table->string('status', 12)->index();

                  $table->dateTime('created_at');
                  $table->dateTime('updated_at');
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
              ->drop('purchase_log');
    }
}
