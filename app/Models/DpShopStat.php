<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpShopStat extends Model
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
    protected $table = 'dp_shop_stats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id',                          // 店铺ID
        'goods_total_sales',                // 商品的总销量
        'goods_month_sales',                // 30天内商品的销量
        'order_total_num',                  // 订单总量
        'order_violation_num',              // 订单违规数量
        'stat_date',                        // 统计日期
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
     * 所属店铺
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo(DpShopInfo::class, 'shop_id', 'shopId');
    }
}
