<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpActivity extends Model
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
    protected $table = 'dp_activities';

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
        'id',                      // 活动id
        'area_id',                 // 片区id
        'activity_type_id',        // 活动类型id
        'start_time',              // 活动开始时间
        'end_time',                // 活动结束时间
        'shop_type_ids',           // 店铺类型ids集合
        'starter_id',              // 活动发起者id
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 对应活动商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityGoods()
    {
        return $this->hasMany(DpActivityGoods::class, 'activity_id', 'id');
    }
}
