<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpDailyNewsInfo extends Model
{
    use SoftDeletes;

    // 状态 status
    const NOT_AUDIT  = 1;     // 未审核
    const PASS_AUDIT = 2;     // 已审核
    const SEND_YES   = 3;     // 已发送

    // 提供前台的删除TAG
    const NOT_DELETE = 1;       // 未删除
    const DELETE     = 2;       // 已删除

    // 新闻类型 5已定义，不可再定义
    const BULK_PURCHASING = 1;      // 团购
    const NEW_PRODUCT     = 2;      // 新品
    const HOT_SALE        = 3;      // 热门
    const PRICE_LIST      = 4;      // 涨跌榜
    const RECOMMEND_GOODS = 6;      // 商品推荐榜
    const SECKILL_GOODS   = 7;      // 秒杀商品

    // 新闻类型数组
    protected static $showTypeArr = [
        self::BULK_PURCHASING => '团购',
        self::NEW_PRODUCT     => '新品',
        self::HOT_SALE        => '热门',
        self::PRICE_LIST      => '涨跌榜',
        self::RECOMMEND_GOODS => '推荐榜',
        self::SECKILL_GOODS   => '秒杀商品',
    ];

    // 获取新闻类型数组
    public static function getShowTypeArr()
    {
        return self::$showTypeArr;
    }

    /**
     * 获取新闻类型名称
     *
     * @param int $showTypeId 类型ID
     *
     * @return string
     */
    public static function getShowTypeName($showTypeId)
    {
        return empty(self::$showTypeArr[$showTypeId])
            ? '未定义'
            : self::$showTypeArr[$showTypeId];
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
    protected $table = 'dp_daily_news_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_id',                          // 大区ID
        'news_type',                        // 信息类型
        'news_title',                       // 标题
        'news_description',                 // 描述
        'news_images',                      // 图片地址
        'url',                              // 链接URL
        'order_num',                        // 排列顺序
        'send_date',                        // 发送日期
        'status',                           // 状态
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
