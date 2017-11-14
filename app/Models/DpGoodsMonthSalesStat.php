<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DpGoodsStat.
 * 商品月销量数据统计
 * @package App\Models
 */
class DpGoodsMonthSalesStat extends Model
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
    protected $table = 'dp_goods_month_sales_stats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id',                      // 店铺ID
        'goods_id',                     // 商品ID
        'total_sales',                  // 总销量
        'online_total_sales',           // 在线支付总销量
        'offline_total_sales',          // 非在线支付总销量
        'stat_date',                    // 统计年月
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

    /**
     * 所属总销量统计
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goodsTotalSales()
    {
        return $this->belongsTo(DpGoodsTotalSalesStat::class, 'goods_id', 'goods_id');
    }
}
