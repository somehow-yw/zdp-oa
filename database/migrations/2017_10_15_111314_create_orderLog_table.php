<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
            ->create('order_statistics_log',function(Blueprint $table){
                $table->increments('id');
                $table->integer('snapshots_id')->comment('快照表的id');
                $table->string('main_order_code',32)->comment('主订单号');
                $table->string('sub_order_code',32)->comment('子订单号');
                $table->integer('main_status')->comment('操作状态/退款/退货/取消订单');
                $table->integer('sub_status')->comment('操作子状态/退款/退货/取消订单');
                $table->string('reason',32)->nullable()->comment('退款/退货/取消订单理由');
                $table->tinyInteger('operation')->default(0)->comment('是否运营');
                $table->timestamp('updated_at')->comment('快照状态跟新时间');
                $table->timestamp('created_at')->comment('快照创建时间');

                $table->index('snapshots_id','snapshots_id_index');
                $table->index('main_order_code','main_order_code_index');
                $table->index('sub_order_code','sub_order_code_index');
                $table->index('main_status','main_status_index');
                $table->index('sub_status','sub_status_index');
                $table->index('reason','reason_index');
                $table->index('operation','operation_id');
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
            ->dropIfExists('order_statistics_log');
    }
}
