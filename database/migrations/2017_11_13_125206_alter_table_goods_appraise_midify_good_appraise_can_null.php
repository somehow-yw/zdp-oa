<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableGoodsAppraiseMidifyGoodAppraiseCanNull extends Migration
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
                $table->tinyInteger('good_appraise')->nullable()->index();
                $table->tinyInteger('has_img')->nullable()->index();
                $table->smallInteger('quality')->nullable()->index();
                $table->tinyInteger('medium_appraise')->nullable()->index();
                $table->tinyInteger('poor_appraise')->nullable()->index();
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
                $table->dropColumn('good_appraise');
                $table->dropColumn('has_img');
                $table->dropColumn('quality');
                $table->dropColumn('medium_appraise');
                $table->dropColumn('poor_appraise');
            });
    }
}
