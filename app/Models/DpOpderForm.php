<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpOpderForm extends Model
{
    // 订单状态 orderact
    const NEW_ORDER                      = 0;                     // 新订单
    const COMMUNICATE_ORDER              = 1;             // 已电联(或已提醒买家付款)
    const CONFIRM_ORDER                  = 2;                 // 卖家已确认
    const DELIVERY_ORDER                 = 3;                // 已发货
    const TAKE_ORDER                     = 4;                    // 已完成(买家收货)
    const INVALID_ORDER                  = 5;               // 无效订单(卖家删除)
    const DEL_ORDER                      = 6;               // 买家删除
    const WITHDRAW_BEING_PROCESSED_ORDER = 7;               // 提现处理中
    const WITHDRAW_ACCOMPLISH_ORDER      = 8;               // 已提现
    const HAVE_EVALUATION                = 9;               // 已做评价 没有使用，但保留
    const TIMEOUT_ORDER                  = 20;              // 超时定单
    const DEPOSIT_BEING_PROCESSED_ORDER  = 101;             // 收款待财务确认(买家付款后财务确认)
    const FREZE_ORDER                    = 102;             // 退款冻结

    const REFUND_ORDER                = 30;              // 退款订单
    const REFUND_ORDER_FROM_PRICE_ING = 31;              // 申请退款
    const REFUND_ORDER_FROM_GOODS_ING = 32;              // 申请退货
    const REFUND_ORDER_GOODS          = 33;       //退货订单

    const SELLER_CANCEL_ORDER_ING = 34;     //卖家申请取消订单
    const SELLER_CANCEL_ORDER     = 35;    //卖家取消订单

    //能够评价订单
    public static $canAppraise = [
        self::TAKE_ORDER,
        self::WITHDRAW_BEING_PROCESSED_ORDER,
        self::WITHDRAW_ACCOMPLISH_ORDER,
        self::HAVE_EVALUATION,
    ];

    //未完成订单
    public static $undoneOrder = [
        self::NEW_ORDER,
        self::COMMUNICATE_ORDER,
        self::CONFIRM_ORDER,
        self::DELIVERY_ORDER,
        self::DEPOSIT_BEING_PROCESSED_ORDER,
    ];

    // 订单的付款方式 method
    const ORDER_PAY_METHOD_COMPANY   = 1;           // 付款到平台
    const ORDER_PAY_METHOD_DRIVER    = 2;          // 上车收钱
    const ORDER_PAY_METHOD_NEGOTIATE = 3;           // 自行协商
    const ORDER_PAY_METHOD_SELL      = 4;           // 打款给商家
    const ORDER_PAY_COD              = 5;                   // 货到付款
    const CENTRALIZED_PURCHASE       = 6;            // 集中采购

    // 购买方式 buy_way
    const NORMAL_BUY      = 1;  // 正常方式
    const CENTRALIZED_BUY = 2;  // 集中采购

    // 订单付款状态 method_act
    const ORDER_PAY_STATUS_NOT            = -1;             // 非平台付款
    const ORDER_PAY_STATUS_WAIT           = 0;              // 等待付款
    const ORDER_PAY_STATUS_RECEIVE        = 1;              // 已付款
    const ORDER_PAY_STATUS_SELL_TAKE      = 2;              // 已结算(卖家已提取)
    const ORDER_PAY_STATUS_RECEIVE_ONLINE = 10;             // 已付款(在线支付) 运营确认后变更为"已付款"

    // 物流方式 delivery
    const ORDER_LOGISTICS_COMPANY    = 1;           // 平台配送
    const ORDER_LOGISTICS_TAKE_GOODS = 2;           // 自己有车
    const ORDER_LOGISTICS_SELLER     = 3;           // 卖家找车
    const ORDER_LOGISTICS_OTHER      = 4;           // 配送到店(其它)

    // 物流公司配置
    const ZDP_LOGISTICS = 1;    // 冷链快递

    // 是否有效订单
    const VALID_ORDER   = 1;                        // 有效
    const NULLITY_ORDER = 0;                        // 无效

    // 买家付款渠道 payment_method
    const PAYMENT_TYPE_ALIPAY               = 1;               // 支付宝
    const PAYMENT_TYPE_WE_CHAT              = 3;               // 微信-转账
    const PAYMENT_TYPE_BANK                 = 4;               // 银行打款
    const PAYMENT_TYPE_ONLINE_WX            = 11;              // 微信-在线支付
    const PAYMENT_TYPE_ONLINE_BAOFOO        = 12;          // 宝付-在线支付(快捷)
    const PAYMENT_TYPE_ONLINE_BAOFOO_WECHAT = 13;          // 宝付-在线支付(微信)
    const PAYMENT_TYPE_IOUS                 = 14;           // 冻品白条支付

    // 资金位置
    const MONEY_PLACE_ALIPAY    = 1;             // 支付宝
    const MONEY_PLACE_BANK_ABC  = 2;             // 农行
    const MONEY_PLACE_WX        = 3;             // 微信
    const MONEY_PLACE_FROM_PLAY = 4;       // 打款到银行卡
    const MONEY_PLACE_BAOFOO    = 5;       // 宝付平台

    // 买家前端页面订单显示详细状态
    const BUY_PAGE_STATUS_WAIT_PAY         = 1;          // 待付款
    const BUY_PAGE_STATUS_WAIT_SHIPMENT    = 2;          // 待发货
    const BUY_PAGE_STATUS_ALREADY_SHIPMENT = 3;          // 已发货
    const BUY_PAGE_STATUS_ALREADY_TAKE     = 4;          // 已完成(显示状态，包括已收货和提现)
    const BUY_PAGE_STATUS_CANCEL           = 5;          // 已取消
    const BUY_PAGE_STATUS_APPRAISE         = 6;          // 退款中
    const BUY_PAGE_STATUS_REFUND           = 7;          // 已退款
    const BUY_PAGE_STATUS_WITHDRAW         = 8;          // 可提现
    const BUY_PAGE_STATUS_WITHDRAWING      = 9;          // 提现中
    const BUY_PAGE_STATUS_WITHDRAWED       = 10;         // 已提现
    const BUY_PAGE_STATUS_RETURN           = 11;         // 退货中
    const BUY_PAGE_STATUS_RETURNING        = 12;         // 已退货
    const BUY_PAGE_STATUS_ORDER_CLOSE      = 13;         // 交易关闭(包括卖家删除，退货和退款)

    const BUY_PAGE_STATUS_BUYER_APPLY_REFUND        = 14;         // 买家申请退款
    const BUY_PAGE_STATUS_SELLER_REFUSE_REFUND      = 15;         // 卖家拒绝退款
    const BUY_PAGE_STATUS_BUYER_APPLY_RETURN        = 16;         // 买家申请退货
    const BUY_PAGE_STATUS_SELLER_REFUSE_RETURN      = 17;         // 卖家拒绝退货
    const BUY_PAGE_STATUS_WAIT_SELLER_REFUND        = 18;         // 等待卖家退款
    const BUY_PAGE_STATUS_WAIT_BUYER_RETURN         = 19;         // 等待买家退货
    const BUY_PAGE_STATUS_SELLER_APPLY_CANCEL_ORDER = 20;          // 卖家申请取消订单
    const BUY_PAGE_STATUS_BUYER_REFUSE_CANCEL_ORDER = 21;          // 买家拒绝申请取消

    const BUY_PAGE_STATUS_JI_WAIT_SELLER_OFFER = 22;         // 集采，等待卖家报价
    const BUY_PAGE_STATUS_JI_WAIT_BUYER_PAY    = 23;         // 集采，等待买家付款
    const BUY_PAGE_STATUS_JI_ALREADY_BUYER_PAY = 26;         // 集采，买家已付款

    const BUY_PAGE_STATUS_CONSULT_WAIT_SHIPMENT    = 24;         //自行协商-待发货
    const BUY_PAGE_STATUS_CONSULT_ALREADY_SHIPMENT = 25;         //自行协商-待收货


    public static $refundAndReturn = [
        self::BUY_PAGE_STATUS_BUYER_APPLY_REFUND,           // 买家申请退款
        self::BUY_PAGE_STATUS_SELLER_REFUSE_REFUND,         // 卖家拒绝退款
        self::BUY_PAGE_STATUS_BUYER_APPLY_RETURN,           // 买家申请退货
        self::BUY_PAGE_STATUS_SELLER_REFUSE_RETURN,         // 卖家拒绝退货
        self::BUY_PAGE_STATUS_WAIT_SELLER_REFUND,           // 等待卖家退款
        self::BUY_PAGE_STATUS_WAIT_BUYER_RETURN,            // 等待买家退货
        self::BUY_PAGE_STATUS_SELLER_APPLY_CANCEL_ORDER,    //卖家申请取消订单
        self::BUY_PAGE_STATUS_BUYER_REFUSE_CANCEL_ORDER,    //买家拒绝申请取消订单
    ];

    //买家前端请求状态
    const REQUEST_BUY_PAGE_STATUS_WAIT_PAY         = 1;             // 待付款
    const REQUEST_BUY_PAGE_STATUS_WAIT_SHIPMENT    = 2;         // 待发货
    const REQUEST_BUY_PAGE_STATUS_ALREADY_SHIPMENT = 3;         // 已发货
    const REQUEST_BUY_PAGE_STATUS_WAIT_APPRAISE    = 4;         // 待评价
    const REQUEST_BUY_PAGE_STATUS_REFUND_RETURN    = 5;         // 退款退货

    // 买家前端订单评价状态
    const PAGE_APPRAISE     = 1;                // 已评价
    const PAGE_NOT_APPRAISE = 2;            // 未评价(就是可以评价)
    const PAGE_NOT_EXECUTE  = 3;             // 不可评价
    const SUPERADDITION     = 4;                // 可追评

    // 收货(或评价重置)后可评价的最长时间(天)
    const END_APPRAISE_DAY = 15;
    // 可追评的最长时间(天)
    const END_ADD_APPRAISE_DAY = 7;

    // 是否转接的订单 order_transfer
    const TRANSFER_NOT = 1;                         // 非转接
    const TRANSFER     = 2;                         // 已转接

    // 备货状态(只有转接的订单才有用)
    const PREPARE_NOT = 1;                      // 未备货
    const PREPARE     = 2;                      // 已备货

    // 订单优惠方式 coupons
    const COUPON_NOT       = 0;                     // 没有优惠
    const COUPON_DISCOUNT  = 1;                     // 优惠券
    const DIAMOND_DISCOUNT = 2;                     // 钻石

    // 默认的最大提醒次数
    const REMIND_TIME = 3;

    //订单是否有评价 has_appraise
    const HAS_NOT_APPRAISE   = 0;   // 订单无评价
    const HAS_APPRAISE       = 1;       // 订单有评价
    const HAS_APPRAISE_RESET = 2;       // 订单评价被重置
    const HAS_APPRAISE_DEL   = 3;       // 订单评价被删除

    protected static $payMethod = [
        self::ORDER_PAY_METHOD_COMPANY   => '付款到平台',
        self::ORDER_PAY_METHOD_DRIVER    => '上车收钱',
        self::ORDER_PAY_METHOD_NEGOTIATE => '自行协商',
        self::ORDER_PAY_METHOD_SELL      => '打款给商家',
        self::ORDER_PAY_COD              => '货到付款',
        self::CENTRALIZED_PURCHASE       => '集中采购',
    ];

    public static function getPayMethodName($payNum)
    {
        return empty(self::$payMethod[$payNum])
            ? '未定义'
            : self::$payMethod[$payNum];
    }

    public static function getPayName($payMethod)
    {
        switch ($payMethod) {
            case self::PAYMENT_TYPE_ALIPAY:
                return '支付宝';
                break;
            case self::PAYMENT_TYPE_WE_CHAT:
                return '微信-转账';
                break;
            case self::PAYMENT_TYPE_BANK:
                return '银行打款';
                break;
            case self::PAYMENT_TYPE_ONLINE_WX:
                return '微信-在线支付';
                break;
            case self::PAYMENT_TYPE_ONLINE_BAOFOO:
                return '宝付-在线支付(快捷)';
                break;
            case self::PAYMENT_TYPE_ONLINE_BAOFOO_WECHAT:
                return '宝付-在线支付(微信)';
                break;
            case self::PAYMENT_TYPE_IOUS:
                return '冻品白条支付';
                break;
            default:
                return '未知';
        }
    }

    public static function getMethodLocation($payMethod)
    {
        switch ($payMethod) {
            case self::PAYMENT_TYPE_ALIPAY:
                // 支付宝
                return self::MONEY_PLACE_ALIPAY;
                break;
            case self::PAYMENT_TYPE_WE_CHAT:
            case self::PAYMENT_TYPE_ONLINE_WX:
            case self::PAYMENT_TYPE_ONLINE_BAOFOO_WECHAT:
                // 微信
                return self::MONEY_PLACE_WX;
                break;
            case self::PAYMENT_TYPE_BANK:
            case self::PAYMENT_TYPE_IOUS:
                // 银行打款 农行
                return self::MONEY_PLACE_BANK_ABC;
                break;
            case self::PAYMENT_TYPE_ONLINE_BAOFOO:
                // 宝付快捷支付 宝付平台
                return self::MONEY_PLACE_BAOFOO;
                break;
            default:
                return self::MONEY_PLACE_WX;
        }
    }

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
    protected $table = 'dp_opder_form';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codenumber',
        // 主订单号 会员ID+时间戳+8位随机串
        'uid',
        // 买家ID
        'uopenid',
        // 买家OPENID
        'real_name',
        // 买家会员真实姓名
        'user_tel',
        // 买家会员联系电话
        'address',
        // 收货地址
        'contacts',
        // 收货联系人
        'contact_tel',
        // 联系电话
        'license_plates',
        // 车牌号
        'vehicle_location',
        // 停放位置
        'driver_tel',
        // 司机电话
        'shipment_time',
        // 买家要求的装车时间 字符串 格式如：2016-02-13 14:00:00 或 联系司机
        'shopid',
        // 卖家商铺ID
        'order_code',
        // 分订单号 主订单号加 -0 等
        'good_num',
        // 商品个数
        'good_count',
        // 商品总量
        'total_price',
        // 订单总价 decimal(11,2)
        'relief_amount',
        // 订单减免金额 decimal(11,2)
        'coupons',
        // 减免方式  0=未使用任何优惠  1=使用了优惠券  2=使用了钻石(积分)
        'method',
        // 付款方式
        'delivery',
        // 配送方式
        'method_act',
        // 付款状态
        'orderact',
        // 订单状态
        'addtime',
        // 订单生成时间 timestamp CURRENT_TIMESTAMP
        'buy_realpay',
        // 实际付款金额 decimal(11,2) 0
        'payment_method',
        // 买家付款渠道
        'buy_payment_bank',
        // 资金存放位置  (如：XXX银行-XXX账号；支付宝；微信)  没有为null
        'buy_payment_handler',
        // 付款处理人  运营后台处理人员（如：晓欣）
        'buy_payment_note',
        // 运营处理付款时的备注 没有为null
        'method_datetime',
        // [datetime NULL] 付款确认时间 财务确认后更改
        'order_confirm',
        // [datetime NULL] 订单卖家确认时间或财务确认收款时间
        'order_clear',
        // [datetime NULL] 订单出货时间
        'order_received',
        // [datetime NULL] 订单收货时间
        'reason',
        // 卖家取消订单理由 由于目前没有整单取消功能，没有使用
        'message',
        // 买家留言，没有使用
        'logistic_num',
        // 物流编号(发货号)
        'formip',
        // 买家IP，不准
        'order_count',
        // 是否有效订单  0=无效  1=有效
        'order_transfer',
        // 是否转接的订单
        'has_appraise',
        //订单是否评价
        'buy_way',
        // 订单购买(采购)方式
        'anonymity_appraise',
        // 是否是匿名评价：0代表不是匿名；1代表匿名
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
     * 根据支付单状态返回订单的支付状态
     *
     * @param $paymentStatus
     *
     * @return int
     */
    public static function getStatusByPaymentStatus($paymentStatus)
    {
        switch ($paymentStatus) {
            case 1:
                return self::ORDER_PAY_STATUS_RECEIVE_ONLINE;
            case 10:
                return self::ORDER_PAY_STATUS_RECEIVE;
            case 20:
                return self::ORDER_PAY_STATUS_WAIT;
            default:
                return $paymentStatus;
        }
    }

    /**
     * 对应订单商品表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderGoods()
    {
        return $this->hasMany(DpCartInfo::class, 'coid', 'order_code');
    }

    /**
     * 定义会员的相对应关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(DpShangHuInfo::class, 'uid', 'shId');
    }

    /**
     * 定义店铺的相对应关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo(DpShopInfo::class, 'shopid', 'shopId');
    }

    /**
     * 所属发货号(物流编号)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function batchNo()
    {
        return $this->belongsTo(DpDeliverGoodsCode::class, 'logistic_num',
            'logistic_num');
    }

    /**
     * 支付明细
     */
    public function payment_details()
    {
        return $this->hasMany(PaymentDetail::class, 'main_order_no',
            'codenumber');
    }

    /**
     * 定义商品评价的相对应关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function goodsAppraise()
    {
        return $this->hasMany(DpGoodsAppraises::class, 'sub_order_no',
            'order_code');
    }

    /**
     * 定义店铺评价的相对应关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderAppraise()
    {
        return $this->belongsTo(DpServiceAppraises::class, 'order_code',
            'sub_order_no');
    }


    /**
     * 关联退款退货子订单表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderOperation()
    {
        return $this->hasMany(DpOrderOperation::class, 'order_code',
            'order_code');
    }

    /**
     * 关联订单日志
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderLog()
    {
        return $this->hasMany(DpOrderSnapshot::class, 'sub_order_no',
            'order_code');
    }


    /**
     * 定义评价日志的相对应关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function appraiseLog()
    {
        return $this->hasMany(DpAppraiseDisposeLog::class, 'order_goods_id', 'order_code');
    }
}
