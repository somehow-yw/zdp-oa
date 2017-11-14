<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpGoods extends Model
{
    // 商品状态 shenghe_act
    const STATUS_AUDIT  = 1;        // 待审核
    const STATUS_NORMAL = 2;        // 已审核
    const STATUS_CLOSE  = 3;        // 已下架
    const STATUS_DEL    = 4;        // 已删除

    // 是否已转移商品 transfer
    const TRANSFER     = 1;     // 已转移
    const NOT_TRANSFER = 0;     // 未转移
    const SHIELDING    = 2;     // 屏蔽转移

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
    protected $table = 'dp_goods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sortid',           // 分类id
        'gname',            // 商品名称
        'origin',           // 产地，字符串
        'brand',            // 品牌，字符串
        'goods_key',        // 关键字，','分隔，用于搜索
        'boosid',           // shangHuInfo.shId，大老板id
        'shopid',           // shopInfo.shopId
        'shid',             // shangHuInfo.shId，添加人id
        'adminid',          // 后台管理员id
        'addtime',          // timestamp，添加时间
        'shenghe_act',      // 商品状态, please see STATUS_XXXX
        'buygood',          // 求购纪录id
        'gengxin_time',     // datetime，最后一次更新商品时间
        'picnum',           // 商品图片数量
        'inspection_report',// 商品检验报告图片
        'suitableids',      // 商品适合场景，','分隔
        'suitablenames',    // 商品适合场景名称，','分隔
        'integralall_num',  // 添加商品时送的积分数
        'jianjie',          // 商品简介
        'transfer',         // 是否已转移商品
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

    //============================
    //      Relations
    //============================
    /**
     * 对应商品基本属性表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsAttribute()
    {
        return $this->hasOne(DpBasicAttribute::class, 'goodsid', 'id');
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
     * 对应每日推文商品表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dailyNewsGoods()
    {
        return $this->hasMany(DpDailyNewsGoodsInfo::class, 'goods_id', 'id');
    }

    /**
     * 对应每日推文推荐商品表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function dailyNewsRecommendGoods()
    {
        return $this->hasOne(DpDailyNewsGoodsInfo::class, 'goods_id', 'id');
    }

    /**
     * 对应活动商品表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityGoods()
    {
        return $this->hasMany(DpActivityGoods::class, 'goods_id', 'id');
    }
}
