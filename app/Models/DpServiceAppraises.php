<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpServiceAppraises extends Model
{
    use SoftDeletes;
    // 卖家服务星级
    const SELL_SERVICE_ONE = 1;          // 一星
    const SELL_SERVICE_TWO = 2;          // 二星
    const SELL_SERVICE_THREE = 3;        // 三星
    const SELL_SERVICE_FOUR = 4;         // 四星
    const SELL_SERVICE_FIVE = 5;         // 五星

    // 出货速度星级
    const DELIVERY_SPEED_ONE = 1;          // 一星
    const DELIVERY_SPEED_TWO = 2;          // 二星
    const DELIVERY_SPEED_THREE = 3;        // 三星
    const DELIVERY_SPEED_FOUR = 4;         // 四星
    const DELIVERY_SPEED_FIVE = 5;         // 五星

    // 平台服务星级
    const SERVICE_ONE = 1;          // 一星
    const SERVICE_TWO = 2;          // 二星
    const SERVICE_THREE = 3;        // 三星
    const SERVICE_FOUR = 4;         // 四星
    const SERVICE_FIVE = 5;         // 五星

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
    protected $table = 'dp_service_appraises';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'buyers_id',                // 评价者ID
        'area_id',                  // 评价者所在片区
        'sell_shop_id',             // 卖家店铺ID
        'sub_order_no',             // 子订单编号
        'sell_service',             // 卖家服务星级
        'delivery_speed',           // 卖家发货速度星级
        'service_platform',         // 平台服务满意度
        'shop_score',               // 卖家获得的店铺分
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    // 商品评价的关联
    public function goodsAppraise()
    {
        return $this->hasMany(DpGoodsAppraises::class, 'sub_order_no', 'sub_order_no');
    }
}
