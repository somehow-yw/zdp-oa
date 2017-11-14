<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');                                         // 操作员ID
            $table->string('user_name', 20);                                    // 操作员名称
            $table->string('login_name', 20);                                   // 操作员登录名称
            $table->string('route_uses', 100);                                  // 操作路由
            $table->date('statistical_date');                                   // 操作日期
            $table->integer('statistical_time')->unsigned()->default(1);        // 操作次数
            $table->string('user_ip', 64);                                      // 操作IP
            $table->timestamps();

            $table->unique(['user_id', 'route_uses', 'statistical_date', 'user_ip']);
            $table->index('login_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_logs');
    }
}
