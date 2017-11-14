<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Exceptions\AppException;
use App\Exceptions\Goods\ActivityException;

/**
 * Class DpGoodsBasicAttribute.
 * 商品基础属性表
 * @package App\Models
 */
class DpGoodsBasicAttribute extends Model
{
    // 商品最低价格(必须大于此价格)
    const GOODS_MIN_PRICE = 1;

    // 商品类型 TAG
    const GOODS_TAG_MAIN         = 1;  // 主打商品
    const GOODS_TAG_GROUP_BUY    = 2;  // 团购商品
    const GOODS_TAG_LEFTOVER     = 3;  // 尾货
    const GOODS_TAG_SECKILL      = 4;  // 秒杀
    const GOODS_TAG_BUY_GET_FREE = 5;  // 买赠

    // 活动类型
    public static $mapArray = [
        self::GOODS_TAG_GROUP_BUY    => '团购',
        self::GOODS_TAG_SECKILL      => "秒杀",
        self::GOODS_TAG_BUY_GET_FREE => "买赠",
    ];

    // 价格过期时间(频率) price_adjust_frequency 如果是长期，使用这里的配置时间。单位:天
    const PRICE_OVERDUE_MAX_TIME = 30;

    // 价格过期具体时间段
    const PRICE_OVERDUE_TIME_SPECIFY = '09:30:00';

    // 是否推荐商品 recommend
    const RECOMMEND     = 1;    // 是
    const NOT_RECOMMEND = 0;    // 否

    // 商品计价单位 meter_unit
    const GOODS_UNIT_JIAN = 0;     // 件
    const GOODS_UNIT_BAG  = 1;     // 抄码件
    const GOODS_UNIT_KG   = 2;     // 公斤
    //const GOODS_UNIT_TON   = 3;     // 吨
    const GOODS_UNIT_POUCH = 4;     // 袋
    const GOODS_UNIT_OTHER = 100;   // 其它

    protected static $goodsUnitNames = [
        self::GOODS_UNIT_JIAN  => '件',
        self::GOODS_UNIT_BAG   => '抄码件',
        self::GOODS_UNIT_KG    => '公斤',
        //self::GOODS_UNIT_TON  => '吨',
        self::GOODS_UNIT_POUCH => '袋',
    ];

    // 默认库存 inventory
    const DEFAULT_INVENTORY = 9999;

    /**
     * 获取商品单位数组
     *
     * @return array
     */
    public static function getGoodsUnits()
    {
        return self::$goodsUnitNames;
    }

    /**
     * 获得计量单位
     *
     * @param $unitNum
     *
     * @return mixed
     */
    public static function getGoodsUnitName($unitNum)
    {
        return empty(self::$goodsUnitNames[$unitNum])
            ?
            '其它'
            :
            self::$goodsUnitNames[$unitNum];
    }

    /**
     * 获得活动类型
     *
     * @param $id
     *
     * @return mixed
     * @throws AppException
     */
    public static function getActivityTypeNameById($id)
    {
        $mapArray = self::$mapArray;
        if (array_has($mapArray, $id)) {
            return $mapArray[$id];
        } else {
            throw new AppException("没有当前活动类型", ActivityException::ACTIVITY_TYPE_ID_NOT_FOUND);
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
    protected $table = 'dp_goods_basic_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * specs格式说明：
     * [
     *  {"constraint_id":1,
     *   "name":"约束(属性)名称",
     *   "format_type_id":3,
     *   "values":[
     *    {"value":"值","unit":"单位(个)"},{...}
     *    ]
     *   },{...}
     * ]
     */
    protected $fillable = [
        'basicid',                                // 属性ID，旧商品转移时需要写入旧的ID
        'goodsid',                                // int 对应商品表ID
        'group_number',                           // 商品组，区分是否同一个商品的不同属性（非特殊属性）
        'guigei',                                 // string 商品规格
        'specs',                                  // 商品规格信息 JSON
        'xinghao',                                // string 商品型号
        'types',                                  // 商品型号 JSON 格式同商品规格 specs
        'net_weight',                             // string 净重
        'rough_weight',                           // float 商品毛重
        'meat_weight',                            // float 解冻后约重
        'meter_unit',                             // int 计量单位
        'goods_price',                            // decimal 商品价格 现出售价格
        'inventory',                              // int 库存 日前只有团购、秒杀等去做了库存操作
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
        'auto_shelves_time',                      // datetime 自动上架时间
        'auto_soldout_time',                      // datetime 自动下架时间
        'price_adjust_frequency',                 // 价格过期频率 0=长期 1=每天过期 2=2天过期 ... 直接将时间作用于自动下架时间上
        'remark',                                 // 备注信息，只保留最近一次的备注
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'basicid';

    /**
     * scope预定义语句，后期调用去掉scope
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeDpGoods($query)
    {
        return $query->join('dp_goods_info', 'dp_goods_info.id', '=', $this->table . '.goodsid');
    }

    /**
     * 获取商品对应的图片
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goodsImage()
    {
        return $this->hasMany(DpGoodsPic::class, 'goodsid', 'goods_id');
    }

    /**
     * 对应商品信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function goods()
    {
        return $this->hasOne(DpGoodsInfo::class, 'id', 'goodsid');
    }
}
