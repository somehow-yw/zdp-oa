<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderStatusLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
            ->create('order_status_log',function(Blueprint $table){
                $table->increments('id');
                $table->string('sub_code',32)->comment('子订单号');
                $table->tinyInteger('status')->comment('订单状态');
                $table->tinyInteger('sub_status')->comment('子订单状态');
                $table->timestamp('updated_at')->comment('订单状态跟新时间');
                $table->timestamp('created_at')->comment('订单创建时间');

                $table->index('sub_code','sub_code_index');
                $table->index('status', 'status_index');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::connection(config('bi.connection','mysql_bi'))
            ->dropIfExists('order_status_log');
    }
}
