<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpShopScore extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_zdp_main';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'dp_shop_scores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id',              // 店铺ID
        'rank_score',           // 等级分数
        'shop_rank',            // 店铺当前等级
        'appraise_score',       // 评价总分(包括店铺商品评分)
        'appraise_number',      // 评价项次(表示被评价了多少次星级)
        'goods_appraise_time',  // 商品评价次数
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
