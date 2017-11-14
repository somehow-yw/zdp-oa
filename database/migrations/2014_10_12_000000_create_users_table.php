<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name', 20);                        // 会员姓名
            $table->string('department_id', 50);                    // 所属部门ID
            $table->string('login_name', 16);                       // 登录名，这里为手机号
            $table->tinyInteger('we_chat_binding')->default(1);     // 是否绑定微信
            $table->tinyInteger('user_status')->default(1);         // 会员账户状态
            $table->string('salt', 16);                             // 密码干扰成分（盐）
            $table->string('password', 128);                        // 登录密码
            $table->string('remark', 255);                          // 备注
            $table->rememberToken();                                // 登录时‘记住我’的信息
            $table->timestamps();

            $table->unique('login_name');
            $table->index('user_name');
            $table->index('department_id');
            $table->index('user_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
