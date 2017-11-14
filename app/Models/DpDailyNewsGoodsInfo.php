<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpDailyNewsGoodsInfo extends Model
{
    use SoftDeletes;

    // 提供前台的删除TAG
    const NOT_DELETE = 1;       // 未删除
    const DELETE     = 2;       // 已删除

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
    protected $table = 'dp_daily_news_goods_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goods_id',                         // 商品ID
        'area_id',                          // 大区ID
        'yesterday_sales_num',              // 昨日销量
        'yesterday_price',                  // 昨日价格
        'show_date',                        // 展示日期
        'show_type',                        // 展示类型 对应着 DpDailyNewsInfo->news_type
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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
