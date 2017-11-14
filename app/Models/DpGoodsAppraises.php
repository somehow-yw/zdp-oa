<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpGoodsAppraises extends Model
{
    use SoftDeletes;

    // 处理状态status
    const NORMAL     = 1;       // 正常(未处理)
    const ACCOMPLISH = 2;       // 已处理

    // 前端状态
    const PAGE_STATUS_NORMAL = 1;       // 正常
    const PAGE_STATUS_DEL    = 2;       // 删除

    // 商品质量星级
    const ONE   = 1;            // 一星
    const TWO   = 2;            // 二星
    const THREE = 3;            // 三星
    const FOUR  = 4;            // 四星
    const FIVE  = 5;            // 五星

    // 评价等级
    const GOOD_APPRAISE   = 1;      // 好评
    const MEDIUM_APPRAISE = 2;      // 中评
    const POOR_APPRAISE   = 3;      // 差评

    const ALL_APPRAISE    = 4;      //所有评价
    const HAS_IMG_APPRAISE = 5;      //取得有图评价
    const FATHER_APPRAISE = 0;      //商品父评论

    const IMG_APPRAISE = 1 ; //评论有图
    const NOT_IMG_APPRAISE = 0; //评论无图

    // 好评的星级
    public static $goodAppraiseArr = [
        self::FIVE,
    ];

    // 中评的星级
    public static $mediumAppraiseArr = [
        self::THREE,
        self::FOUR,
    ];

    // 差评的星级
    public static $poorAppraiseArr = [
        self::ONE,
        self::TWO,
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
    protected $table = 'dp_goods_appraises';

    /**
     * The attribut2016-01-02es that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'buyers_id',                // 评价者ID
        'area_id',                  // 评价者所在片区
        'sell_shop_id',             // 卖家店铺ID
        'goods_id',                 // 商品ID
        'sub_order_no',             // 子订单编号
        'order_goods_id',           // 订单商品ID
        'quality',                  // 商品质量星级
        'praises_num',              //点赞总数
        'content',                  // 评价内容
        'replay',                   //回复
        'shop_score',               // 店铺分变化
        'status',                   // 处理状态
        'hasImg',                   //该条评论是否有图片
        'pid',
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

    /**
     * 评价等级的获取
     *
     * @param $starNum int 所评的星数（分数）
     *
     * @return int|null
     */
    public static function getAppraiseGrade($starNum)
    {
        if (in_array($starNum, self::$goodAppraiseArr)) {
            return self::GOOD_APPRAISE;
        } elseif (in_array($starNum, self::$mediumAppraiseArr)) {
            return self::MEDIUM_APPRAISE;
        } elseif (in_array($starNum, self::$poorAppraiseArr)) {
            return self::POOR_APPRAISE;
        } else {
            return null;
        }
    }

    // 订单服务评价的对应
    public function serviceAppraise()
    {
        return $this->belongsTo(DpServiceAppraises::class, 'sub_order_no', 'sub_order_no');
    }

    /**
     * 对评论的图片表
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appraiseImg()
    {
        return $this->hasMany(DpAppraiseImgs::class, 'appraise_id', 'id');
    }

    /**
     * 对应货物
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goods_id', 'id');
    }

    /**
     * 评论对应的商户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer()
    {
        return $this->belongsTo(DpShangHuInfo::class, 'buyers_id', 'shId');
    }

    /**
     * 评论的点赞
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function praise()
    {
        return $this->hasMany(DpPraiseNum::class, 'appraises_id', 'id');
    }

    /**评价对应商店
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop(){
        return $this->belongsTo(DpShopInfo::class,'goods_id','shopId');
    }
}
