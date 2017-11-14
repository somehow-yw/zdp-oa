<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentActionMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_action_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('department_id');               // 部门ID
            $table->string('privilege_tag', 15);            // 权限标记
            $table->timestamps();

            $table->unique(['department_id', 'privilege_tag']);
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('department_action_maps');
    }
}
