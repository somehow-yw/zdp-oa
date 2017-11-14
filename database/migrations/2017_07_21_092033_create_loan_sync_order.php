<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanSyncOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection', 'mysql_bi'))
              ->create('loan_sync_order', function (Blueprint $table) {
                  $table->increments('id');

                  $table->date('date')->index();
                  $table->string('province', 32)->index();
                  $table->string('city', 32)->index();
                  $table->string('district', 32)->nullable()->index();

                  $table->integer('ious_id')->index()->comment('贷款id');
                  $table->integer('amount')->index()->comment('贷款金额');

                  $table->integer('shop_id')->index()->comment('店铺id');
                  $table->string('shop_name',64)->comment('店铺名字');
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
              ->drop('loan_sync_order');
    }
}
