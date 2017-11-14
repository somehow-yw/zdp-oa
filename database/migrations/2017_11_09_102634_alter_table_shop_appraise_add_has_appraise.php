<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShopAppraiseAddHasAppraise extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->table('shop_appraises', function (Blueprint $table) {
                $table->tinyInteger('has_appraise')->index()->comment('订单是否被评价');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('bi.connection'))
            ->table('shop_appraises', function (Blueprint $table) {
                $table->dropColumn('has_appraise');
            });
    }
}
