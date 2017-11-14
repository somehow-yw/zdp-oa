<?php

namespace App\Models;

use App\Exceptions\AppException;
use App\Exceptions\Goods\ActivityException;
use Illuminate\Database\Eloquent\Model;

class DpBasicAttribute extends Model
{
    // 商品类型 TAG
    const GOODS_TAG_MAIN         = 1;  // 主打商品
    const GOODS_TAG_GROUP_BUY    = 2;  // 团购商品
    const GOODS_TAG_LEFTOVER     = 3;  // 尾货
    const GOODS_TAG_SECKILL      = 4;  // 秒杀
    const GOODS_TAG_BUY_GET_FREE = 5;  // 买赠

    public static function getActivityTypeNameById($id)
    {
        $map_array = [
            self::GOODS_TAG_GROUP_BUY    => '团购',
            self::GOODS_TAG_SECKILL      => "秒杀",
            self::GOODS_TAG_BUY_GET_FREE => "买赠",
        ];
        if (array_has($map_array, $id)) {
            return $map_array[$id];
        } else {
            throw new AppException("没有当前活动类型", ActivityException::ACTIVITY_TYPE_ID_NOT_FOUND);
        }
    }

    // 商品计价单位
    const GOODS_UNIT_JIAN  = 0;    // 件
    const GOODS_UNIT_BAG   = 1;     // 袋
    const GOODS_UNIT_KG    = 2;      // 公斤
    const GOODS_UNIT_TON   = 3;     // 吨
    const GOODS_UNIT_OTHER = 100; // 其它

    protected static $goodsUnitNames = [
        self::GOODS_UNIT_JIAN  => '件',
        self::GOODS_UNIT_BAG   => '袋',
        self::GOODS_UNIT_KG    => '公斤',
        self::GOODS_UNIT_TON   => '吨',
        self::GOODS_UNIT_OTHER => '其它',
    ];

    public static function getGoodsUnitName($unitNum)
    {
        return empty(self::$goodsUnitNames[$unitNum])
            ?
            self::$goodsUnitNames[self::GOODS_UNIT_OTHER]
            :
            self::$goodsUnitNames[$unitNum];
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
    protected $table = 'dp_basic_attribute';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goodsid',                                // int 对应商品表ID
        'guigei',                                 // string 商品规格
        'xinghao',                                // string 商品型号
        'net_weight',                             // string 净重
        'meter_unit',                             // int 计量单位
        'goods_price',                            // decimal 商品价格 现出售价格
        'yuan_price',                             // decimal 商品原价 团购有
        'subsidy_price',                          // decimal 补贴价 团购会有补贴
        'inventory',                              // int 库存 团购有
        'tag',                                    // int 商品的类型，如 主打 团购
        'recommend',                              // int 是否推荐商品 0=非推荐商品 1=已推荐商品
        'recommtime',                             // datetime 推荐时间
        'recomm_uid',                             // datetime 推荐人ID
        'opder_num',                              // int 表示已生成定单的商品数量
        'xiadan_num',                             // int 表示已加入购的车的商品数量
        'sell_num',                               // int 表示最终成交的商品数量
        'fromsell_num',                           // int 起卖数量 团购有
        'click_num',                              // int 点击数量 没有计
        'previous_price',                         // decimal 更改价格后的前一次价格
        'end_price_change_time',                  // datetime最后更改价格的时间
        'auto_shelves_time',                      // datetime 自动上架时间 未用
        'auto_soldout_time',                      // datetime 自动下架时间 未用
        'other_info',                             // string 其它信息说明
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
    protected $primaryKey = 'basicid';
}
