<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpDailyNewsRecommendGoods extends Model
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
    protected $table = 'dp_daily_news_recommend_goods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goods_id',                         // 商品ID
        'area_id',                          // 大区ID
        'seller_shop_name',                 // 卖家店铺名
        'sort_value',                       // 排序值 数值越大排名越靠前
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

    /**
     * 所属商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goods_id', 'id');
    }
}
