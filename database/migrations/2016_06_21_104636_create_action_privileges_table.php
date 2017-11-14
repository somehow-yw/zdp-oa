<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionPrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_privileges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id');                                       // 父权限ID
            $table->string('nodes', 100);                                       // 权限节点ID串
            $table->string('privilege_name', 50);                               // 权限名称
            $table->string('privilege_tag', 100);                               // 权限代号（标记）
            $table->tinyInteger('navigate_rank')->unsigned()->default(0);       // 导航级别
            $table->string('route', 255);                                       // URL路由
            $table->tinyInteger('status')->unsigned()->default(1);              // 状态
            $table->tinyInteger('sort')->default(1);                            // 菜单排序
            $table->string('remark', 255);                                      // 备注
            $table->timestamps();

            $table->unique('privilege_tag');
            $table->index('parent_id');
            $table->index('privilege_name');
            $table->index('status');
            $table->index(['navigate_rank', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('action_privileges');
    }
}
