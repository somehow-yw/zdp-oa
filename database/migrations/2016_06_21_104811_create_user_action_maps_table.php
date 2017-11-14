<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActionMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_action_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');                     // 操作员ID
            $table->string('privilege_tag', 100);           // 权限标记
            $table->timestamps();

            $table->unique(['user_id', 'privilege_tag']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_action_maps');
    }
}
