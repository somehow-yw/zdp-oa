<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceHandleLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_handle_log', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('sp_id');
            $table->integer('uid');
            $table->tinyInteger('operate');

            $table->timestamp('created_at')
                  ->default(\DB::raw('CURRENT_TIMESTAMP'));

            $table->index('sp_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_handle_log');
    }
}
