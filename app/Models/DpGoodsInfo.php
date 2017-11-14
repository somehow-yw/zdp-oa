<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DpGoodsInfo.
 * 商品信息
 *
 * @package App\Models
 */
class DpGoodsInfo extends Model
{
    // 商品状态 shenghe_act
    const STATUS_AUDIT = 1;       // 待审核
    const STATUS_NORMAL = 2;       // 已审核
    const STATUS_CLOSE = 3;       // 已下架
    const STATUS_DEL = 4;       // 已删除
    const STATUS_REJECT = 5;       // 拒绝
    const STATUS_MODIFY_AUDIT = 6;       // 修改待审核
    const WAIT_PERFECT = 100;     // 待完善的商品（商品转移临时状态）

    // 商品评价统计
   public static $statistics = [
       self::STATUS_NORMAL,
       self::STATUS_CLOSE,
       self::STATUS_DEL,
       self::STATUS_MODIFY_AUDIT
   ];

    // 商品上、下架状态 on_sale
    const GOODS_NOT_ON_SALE = 1;      // 已下架
    const GOODS_SALE = 2;      // 已上架

    // 商品国别（是否水货） smuggle
    const DOMESTIC = 1;     // 国产
    const IMPORT = 2;     // 进口
    const GRAY = 3;     // 水货

    // 是否清真食品 halal
    const HALAL = 1;    // 是
    const NOT_HALAL = 0;    // 否

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
    protected $table = 'dp_goods_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',               // 商品ID，旧商品转移时需要写入旧的ID
        'group_number',     // 商品组，区分是否同一个商品的不同属性（非特殊属性）
        'goods_type_id',    // 商品分类ID
        'sortid',           // 商品分类节点ID串
        'gname',            // 商品名称
        'goods_title',      // 商品标题
        'origin',           // 产地，字符串
        'brand_id',         // 品牌ID
        'brand',            // 品牌，字符串
        'goods_key',        // 关键字，','分隔，用于搜索
        'boosid',           // shangHuInfo.shId，大老板id
        'shopid',           // shopInfo.shopId
        'shid',             // shangHuInfo.shId，添加人id
        'adminid',          // 后台管理员id
        'shenghe_act',      // 商品状态, please see STATUS_XXXX
        'on_sale',          // 商品上、下架状态
        'buygood',          // 求购纪录id
        'halal',            // 是否清真
        'smuggle',          // 国别（是否水货）
        'picnum',           // 商品图片数量
        'integralall_num',  // 添加商品时送的积分数
        'inspection_report',// 商品检验报告图片
        'suitableids',      // 商品适合场景ID串 ','分隔
        'suitablenames',    // 商品适合场景名称 ','分隔
        'jianjie',          // 商品简介
        'audit_time',       // 管理后台审核时间
        'addtime',          // 添加时间
        'gengxin_time',     // 最后一次更新商品时间
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
     * 国别数组
     *
     * @var array
     */
    protected static $smuggleArr = [
        self::DOMESTIC => "国产",
        self::IMPORT   => "进口",
        self::GRAY     => "水货",
    ];

    /**
     * 国别数组的获取
     *
     * @return array
     */
    public static function getSmugglesList()
    {
        return self::$smuggleArr;
    }

    /**
     * 根据国别编号取得国别名称
     *
     * @param $smuggleNum int 国别编号
     *
     * @return string
     */
    public static function getSmuggleName($smuggleNum)
    {
        return empty(self::$smuggleArr[$smuggleNum]) ? '' : self::$smuggleArr[$smuggleNum];
    }

    //============================
    //      Relations
    //============================
    /**
     * 对应商品基本属性表 ID
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function goodsAttribute()
    {
        return $this->hasOne(DpGoodsBasicAttribute::class, 'goodsid', 'id');
    }

    /**
     * 对应商品特殊属性表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function specialAttribute()
    {
        return $this->hasMany(DpGoodsSpecialAttribute::class, 'goodsid', 'id');
    }

    /**
     * 价格体系关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function priceRule()
    {
        return $this->hasMany(DpPriceRule::class, 'goods_id', 'id');
    }

    /**
     * 对应商品基本属性表 group_number 商品组
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsBasicGroup()
    {
        return $this->hasMany(DpGoodsBasicAttribute::class, 'group_number', 'group_number');
    }

    /**
     * 对应商品图片表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsPicture()
    {
        return $this->hasMany(DpGoodsPic::class, 'goodsid', 'id');
    }

    /**
     * 对应商品检验报告图片
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsInspectionReport()
    {
        return $this->hasMany(DpGoodsInspectionReport::class, 'goods_id', 'id');
    }

    /**
     * 对应订单商品表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderGoods()
    {
        return $this->hasMany(DpCartInfo::class, 'goodid', 'id');
    }

    /**
     * 所属店铺
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo(DpShopInfo::class, 'shopid', 'shopId');
    }

    /**
     * 分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(DpGoodsType::class, 'sortid', 'nodeid');
    }

    /**
     * 品牌
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(DpBrands::class, 'brand_id', 'id');
    }

    /**
     * 新分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(DpGoodsType::class, 'goods_type_id', 'id');
    }

    /**
     * 对应每日推文推荐商品表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function dpDailyNewsRecommendGoods()
    {
        return $this->hasOne(DpDailyNewsGoodsInfo::class, 'goods_id', 'id');
    }

    /**
     * 对应月销售统计表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsMonthSalesStat()
    {
        return $this->hasMany(DpGoodsMonthSalesStat::class, 'goods_id', 'id');
    }

    /**
     * 对应总销售统计表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function goodsTotalSalesStat()
    {
        return $this->hasOne(DpGoodsTotalSalesStat::class, 'goods_id', 'id');
    }

    /**
     * 对应月评价统计表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function goodsMonthAppraiseStat()
    {
        return $this->hasOne(DpGoodsMonthAppraiseStat::class, 'goods_id', 'id');
    }

    /**
     * 对应总评价统计表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function goodsTotalAppraiseStat()
    {
        return $this->hasOne(DpGoodsTotalAppraiseStat::class, 'goods_id', 'id');
    }
    //============================
    //      Custom Functions
    //============================
    /**
     * 判断是否真的在售(即该商品对用户可见)
     * 商品状态通过审核且商品已上架
     */
    public function isOnSale()
    {
        return $this->shenghe_act == self::STATUS_NORMAL && $this->on_sale == self::GOODS_SALE;
    }

    /**
     * 获取指定店铺未审核的商品数量
     * 包括新商品及已修改待审核的商品
     *
     * @param $shopId int 店铺ID 默认为全部
     *
     * @return int
     */
    public static function getNotAuditGoodsNum($shopId = 0)
    {
        $goodsStatusArr = [
            self::STATUS_AUDIT,
            self::STATUS_MODIFY_AUDIT,
        ];

        $query = self::whereIn('shenghe_act', $goodsStatusArr);
        if (!empty($shopId)) {
            $query = $query->where('shopid', $shopId);
        }

        return $query->count();
    }
}
