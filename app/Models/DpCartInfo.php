<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpCartInfo extends Model
{
    // 订单商品的状态 good_act
    const SHOP_CART_GOODS                = 0;               // 商品还在购物车(未生成订单)
    const ORDER_GOODS                    = 1;               // 新订单(商品已确认生成订单)
    const CONFIRM_ORDER_GOODS            = 2;               // 卖家已确认
    const DELIVERY_ORDER_GOODS           = 3;               // 已发货
    const TAKE_ORDER_GOODS               = 4;               // 已完成(买家收货)
    const INVALID_ORDER_GOODS            = 5;               // 无效订单(卖家删除)
    const DEL_ORDER_GOODS                = 6;               // 买家删除
    const WITHDRAW_BEING_PROCESSED_ORDER = 7;               // 订单提现处理中
    const WITHDRAW_ACCOMPLISH_ORDER      = 8;               // 订单已提现
    const HAVE_EVALUATION                = 9;               // 已做评价
    const TIMEOUT_ORDER_GOODS            = 20;              // 订单超时
    const REFUND_GOODS                   = 40;              // 商品退款
    const DEPOSIT_BEING_PROCESSED_ORDER  = 101;             // 收款待财务确认(买家付款后财务确认)
    const FREZE_ORDER_GOODS              = 102;             // 退款冻结

    // 商品备货状态(只有转接的订单商品才有用)
    const PREPARE_NOT = 1;                  // 未备货
    const PREPARE     = 2;                  // 已备货

    // 商品评价统计
    public static $statistics = [
        self::TAKE_ORDER_GOODS,
        self::WITHDRAW_BEING_PROCESSED_ORDER,
        self::WITHDRAW_ACCOMPLISH_ORDER,
        self::HAVE_EVALUATION
    ];

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
    protected $table = 'dp_cart_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',                      // 买家shangHuInfo.shId
        'coid',                     // good_act > 0 表示子订单编号号order_code, 其余情况为购物车id
        'shopid',                   // 卖家shopInfo.shopId
        'method',                   // 付款方式，deprecated
        'delivery',                 // 配送方式, deprecated
        'address',                  // 收货地址
        'contacts',                 // 收货联系人, deprecated
        'contact_tel',              // 收货联系人电话, deprecated
        'license_plates',           // 车牌号, deprecated
        'vehicle_location',         // 车辆停放位置, deprecated
        'driver_tel',               // 司机电话, deprecated
        'shipment_time',            // 装车时间, deprecated
        'goodid',                   // 商品id，dp_goods.id
        'bid',                      // 商品基本属性id
        'good_type',                // 商品类型，对应商品属性表tag
        'goods_price',              // 添加到购物车时价格
        'good_new_price',           // 商品下单时价格, 单位: 元
        'good_price_change_time',   // 价格变动时间. datetime
        'good_yact',                // 商品在购物车状态
        'buy_num',                  // 购买数量
        'meter_unit',               // 商品计量单位，用于显示
        'count_price',              // 商品总价，单价 * 数量
        'addtime',                  // 商品添加到购物车时间
        'good_act',                 // 商品在订单的状态，与订单状态
        'reason',                   // 订单商品删除理由
        'ip',                       // 用户ip，ipv4
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
    protected $primaryKey = 'id';

    /**
     * 所对应的商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goodid', 'id');
    }
}
