<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDpMessagesAddUsertype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql_zdp_main')
            ->table('dp_messages',function($table){
                $table->string('messages_type',1)->default('1')->comment('反馈来源：1=>找冻品网；2=>服务商');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_zdp_main')
            ->table('dp_messages',function($table){
                $table->dropColumn('messages_type');
            });
    }
}
