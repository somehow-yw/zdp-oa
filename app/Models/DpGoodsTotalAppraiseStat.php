<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DpGoodsTotalAppraiseStat.
 * 商品评价统计
 * @package App\Models
 */
class DpGoodsTotalAppraiseStat extends Model
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
    protected $table = 'db_goods_total_appraise_stats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id',                      // 店铺ID
        'goods_id',                     // 商品ID
        'total_appraise_num',           // 总评数
        'good_appraise_num',            // 好评数
        'medium_appraise_num',          // 中评数
        'poor_appraise_num',            // 差评数
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
     * 对应月销售统计表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsMonthAppraise()
    {
        return $this->hasMany(DpGoodsMonthAppraiseStat::class, 'goods_id', 'goods_id');
    }
}
