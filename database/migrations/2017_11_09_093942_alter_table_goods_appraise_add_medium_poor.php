<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableGoodsAppraiseAddMediumPoor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('bi.connection'))
            ->table('goods_appraises', function (Blueprint $table) {
                $table->tinyInteger('medium_appraise')->index()->comment('是否中评:中评为1，非中评为0');
                $table->tinyInteger('poor_appraise')->index()->comment('是否差评：差评为1，非差评为0');
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
            ->table('goods_appraises', function (Blueprint $table) {
                $table->dropColumn('medium_appraise');
                $table->dropColumn('poor_appraise');
            });
    }
}
