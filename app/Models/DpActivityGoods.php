<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpActivityGoods extends Model
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
    protected $table = 'dp_activitys_goods';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activity_id',                  // 活动ID
        'goods_id',                     // 商品ID
        'sort_value',                   // 排序值
        'reduction',                    // 折扣金额
        'rule',                         // 活动规则
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 所属活动
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo(DpActivity::class, 'activity_id', 'id');
    }

    /**
     * 所属商品信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goods_id', 'id');
    }
}
