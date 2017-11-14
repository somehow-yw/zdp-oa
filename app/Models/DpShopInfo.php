<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpShopInfo extends Model
{
    // 营业执照是否认证 license_act
    const LICENCE_STATUS_AUDIT  = 0;        // 未认证
    const LICENCE_STATUS_NORMAL = 1;        // 已认证

    // 上线状态 business
    const STATUS_LOCKED = 0;                // 店铺未上线，只能拨打电话
    const STATUS_OPEN   = 1;                // 营业中
    const STATUS_CLOSE  = 2;                // 已关闭，且不能拨打电话

    // 店铺状态 state
    const STATE_NORMAL = 0;                 // 正常
    const STATE_DEL    = 1;                 // 已删除

    // trenchnum 店铺类型
    const SHOP_TYPE_YIPI          = 11;              // 一批商
    const SHOP_TYPE_MANUFACTURERS = 12;

    // 店铺类型 trenchnum
    const YIPI         = 11;                // 一批
    const VENDOR       = 12;                // 厂家
    const ERPI         = 21;                // 二批
    const MIDDLEMEN    = 22;                // 第三方
    const DISTRIBUTORS = 23;                // 配送公司
    const TERMINAL     = 24;                // 终端
    const RESTAURANT   = 25;                // 餐厅
    const SUPERMARKET  = 26;                // 商超零售
    const DRIVER       = 31;                // 司机
    const DIRECT_SELL  = 100;               // 直营

    //供应商
    const SUPPLY_SHOP = 1;
    //供应商
    public static $supplyShop = [
        self::YIPI,
        self::VENDOR
    ];
    //采购商
    const PURCHASE_SHOP = 0;
    //采购商
    public static $purchaseShop = [
        self::ERPI,
        self::MIDDLEMEN,
        self::DISTRIBUTORS,
        self::TERMINAL,
        self::RESTAURANT,
        self::SUPERMARKET
    ];

    //排序方式
    const ZHUCETIME    = 1;
    const JIAOYIMONEY  = 2;
    const APPRAISENUM  = 3;
    const GOODAPPRAISE = 4;

    // 将店铺类型代号转成对应的中文名称存放
    public static $shopTypeName = [
        self::YIPI         => '一批',
        self::VENDOR       => '厂家',
        self::ERPI         => '二批',
        self::MIDDLEMEN    => '第三方',
        self::DISTRIBUTORS => '配送公司',
        self::TERMINAL     => '终端',
        self::RESTAURANT   => '餐厅',
        self::SUPERMARKET  => '商超零售',
        self::DRIVER       => '司机',
        self::DIRECT_SELL  => '直营',
    ];

    // 可以有购物车的店铺类型
    public static function getShopCartTheShopTypes()
    {
        return [
            self::ERPI,
            self::MIDDLEMEN,
            self::DISTRIBUTORS,
            self::TERMINAL,
            self::RESTAURANT,
            self::SUPERMARKET,
            self::DRIVER,
            self::DIRECT_SELL,
        ];
    }

    // 根据店铺类型代号 返回对应的店铺类型代号名称
    public static function getShopTypeName($shopTypeTag)
    {
        return empty(self::$shopTypeName[$shopTypeTag])
            ? '未知类型'
            : self::$shopTypeName[$shopTypeTag];
    }

    // 取得店铺类型数组
    public static function getShopType()
    {
        return self::$shopTypeName;
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
    protected $table = 'dp_shopInfo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shopId',               // int 店铺id
        'laoBanId',             // int 老板id，shangHuInfo.shId
        'dianPuName',           // string 店铺名称
        'cardPic',              // string 名片图片url
        'license_pic',          // string 营业执照图片url
        'license_act',          // int 营业执照状态, see LICENCE_STATUS_XXX
        'jieDanTel',            // string 接单电话
        'pianquId',             // ind 市场id, dp_pianqu.pianquId，海霸王，青白江
        'trenchnum',            // int 店铺类型分组编号
        'province',             // string 省名称
        'city',                 // string 市名称
        'county',               // string 县名称
        'house_number',         // string 门牌号
        'xiangXiDiZi',          // string 详细地址
        'dianpuKey',            // string 店铺关键字 ','分隔，用于搜索
        'dianpuJianJie',        // string 店铺简介
        'beizhu',               // string 店铺备注
        'fangWenNum',           // int 访问量
        'tianJiaNum',           // int 商品天假次数
        'goodsNum',             // int 商品总数，不包含已删除的商品
        'groupNum',             // int 团购商品数量
        'tuijianCount',         // int 总推荐次数
        'syTuijian',            // int 剩余推荐次数
        'tuijian',              // int 推荐状态
        'tjstartTime',
        'tjendTime',
        'tuijianRen',
        'todayGoodsTuijianTime',
        'fadan_act',
        'business',             // int 是否上线, see STATUS_XXXX
        'open_time',            // datetime 每天开门时间
        'date',                 // timestamp 店铺创建时间
        'state',                // int see STATE_XXX
        'old',                  // int 0 - 新用户; 1 - 老用户; 2 -- 老用户但已在新平台使用
        'sale_goods_num',       // 该店铺在售商品数量(对用户可见)
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
    protected $primaryKey = 'shopId';

    /**
     * 对应会员表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->hasMany(DpShangHuInfo::class, 'shopId', 'shopId');
    }

    //============================
    //      Relations
    //============================
    /**
     * 所属市场，例如：青白江，海霸王
     */
    public function market()
    {
        return $this->belongsTo(DpMarketInfo::class, 'pianquId', 'pianquId');
    }

    /**
     * 老板
     */
    public function boss()
    {
        return $this->hasOne(DpShangHuInfo::class, 'shId', 'laobanId');
    }

    /**
     * 商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goods()
    {
        return $this->hasMany(DpGoodsInfo::class, 'shopid', 'shopId');
    }

    /**
     * 积分及等级
     */
    public function score_rank()
    {
        return $this->hasOne(DpShopScore::class, 'shop_id', 'shopId');
    }

    /**
     * 对应店铺统计
     */
    public function shopStat()
    {
        return $this->hasOne(DpShopStat::class, 'shop_id', 'shopId');
    }

    /**对应店铺订单
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order(){
        return $this->hasMany(DpOpderForm::class,'shopid','shopId');
    }

    /**对应店铺评价
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appraise(){
        return $this->hasMany(DpGoodsAppraises::class,'sell_shop_id','shopId');
    }

    /**店铺服务评价
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serAppraise(){
        return $this->hasMany(DpServiceAppraises::class,'sell_shop_id','shopId');
    }
}
