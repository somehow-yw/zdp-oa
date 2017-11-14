<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCreatedAtDpPianquDivide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_zdp_main')
              ->table('dp_pianqu_divide', function (Blueprint $table) {
                  $table->timestamp('created_at')
                        ->default(\DB::raw('CURRENT_TIMESTAMP'))
                        ->common('开通时间');
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
              ->table('dp_pianqu_divide', function (Blueprint $table) {
                  $table->dropColumn('created_at');
              });
    }
}
