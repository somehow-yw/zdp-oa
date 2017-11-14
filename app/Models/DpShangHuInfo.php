<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpShangHuInfo extends Model
{
    // 店铺角色
    const SHOP_BOOS = 0;            // 大老板
    const SHOP_LIBRARY_TUBE = 1;     // 库管
    const SHOP_ORDER_TAKERS = 2;    // 接单员
    const SHOP_PARTNER = 3;         // 小老板(合作伙伴)
    const SHOP_STAFF = 4;           // 普通员工

    // 状态
    const STATUS_UNTREATED = 0;     // 未审核
    const STATUS_PASS = 1;          // 通过审核
    const STATUS_REFUSE = 2;        // 拒绝
    const STATUS_DELETE = 3;        // 已删除

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
    protected $table = 'dp_shangHuInfo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'shId',
        'zhuceTime',
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'shId';

    /**
     * 所属商铺
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo(DpShopInfo::class, 'shopId', 'shopId');
    }
}
