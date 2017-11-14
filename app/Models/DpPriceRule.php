<?php

namespace App\Models;

use App\Utils\MoneyUnitConvertUtil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DpPriceRule.
 * 商品价格体系
 *
 * @package App\Models
 */
class DpPriceRule extends Model
{
    // 价格体系规则配置 price_rule_id
    const NOT_RULE   = 0;     // 没有价格体系
    const BUY_REDUCE = 1;     // 买减（如购买多少件优惠多少钱）
    const BUY_GIVE   = 2;     // 买赠(如购买50件赠1件)

    /**
     * 价格体系规则名称映射
     *
     * @var array
     */
    public static $priceRuleArr = [
        self::NOT_RULE   => '',
        self::BUY_REDUCE => '买减',
    ];

    /**
     * 价格体系规则名称映射，针对前台展示
     *
     * @var array
     */
    public static $priceRuleArrForDisplay = [
        self::NOT_RULE   => [
            'price_rule_id'     => self::NOT_RULE,
            'buy_unit'          => '',
            'show_name'         => '',
            'preferential_unit' => '',
        ],
        self::BUY_REDUCE => [
            'price_rule_id'     => self::BUY_REDUCE,
            'buy_unit'          => '件',
            'show_name'         => '减',
            'preferential_unit' => '元',
        ],
        //        self::BUY_GIVE   => [
        //            'price_rule_id'     => self::BUY_GIVE,
        //            'buy_unit'          => '件',
        //            'show_name'         => '赠',
        //            'preferential_unit' => '件',
        //        ],
    ];

    protected $table = "dp_price_rules";

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_zdp_main';

    protected $fillable = [
        'id',               // 价格体系记录id
        'price_rule_id',    // 价格体系规则id
        'goods_id',         // 商品id
        'buy_num',          // 购买数量
        'preferential',     // 优惠（满减就是金额（单位：分），满赠就是赠送数量)
        'created_at',       // 创建时间
        'updated_at',       // 更新时间
    ];

    public $timestamps = true;

    protected $primaryKey = 'id';

    /**
     * 获取优惠文本
     *
     * @param $priceRuleId  int 价格体系规则ID 配置的常量
     * @param $goodsUnit    string 商品计量单位
     * @param $preferential int 优惠值 金额时单位为分
     *
     * @return string
     */
    public static function getPreferentialText($priceRuleId, $goodsUnit, $preferential)
    {
        switch ($priceRuleId) {
            case self::BUY_REDUCE:
                return MoneyUnitConvertUtil::delNumberPointZero(MoneyUnitConvertUtil::fenToYuan($preferential)) . "元";
            case self::BUY_GIVE:
                return $preferential . $goodsUnit;
            default:
                return '';
        }
    }

    /**
     * 返回价格体系的优惠值 如果是金额返回单位为(分),保存使用
     *
     * @param $priceRuleId       int 优惠类型ID
     * @param $preferentialValue int 优惠值
     *
     * @return int
     */
    public static function convertPreferentialValueForSave($priceRuleId, $preferentialValue)
    {
        switch ($priceRuleId) {
            case self::BUY_REDUCE:
                return MoneyUnitConvertUtil::yuanToFen($preferentialValue);
            default:
                return $preferentialValue;
        }
    }

    /**
     * 转换优惠值用于展示
     *
     * @param $priceRuleId
     * @param $preferentialValue
     *
     * @return string
     */
    public static function convertPreferentialValueForShow($priceRuleId, $preferentialValue)
    {
        switch ($priceRuleId) {
            case self::BUY_REDUCE:
                return MoneyUnitConvertUtil::delNumberPointZero(MoneyUnitConvertUtil::fenToYuan($preferentialValue));
            default:
                return $preferentialValue;
        }
    }

    /**
     * @param $priceRuleId
     * @param $unit
     *
     * @return string
     */
    public static function convertPreferentialUnit($priceRuleId, $unit)
    {
        // 判断是否应转换优惠数值为 元
        if (DpPriceRule::BUY_REDUCE == $priceRuleId) {
            $goodsUnit = '元';
        } else {
            $goodsUnit = $unit;
        }

        return $goodsUnit;
    }

    /**
     * 商品关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goods_id', 'id');
    }

    /**
     * 返回价格体系
     *
     * @param $priceRule      Collection
     *
     * @param $goodsUnit      string 商品单位，为了返回优惠文本
     *
     * @return array
     *
     * [
     *  {
     *  "rule_type":"减",
     *  "rules":[
     *           {
     *             "buy_num":10,
     *             "preferential":“200元"
     *           },
     *          {...}
     *      ]
     *  }
     * ]
     */
    public static function priceRuleHandler($priceRule, $goodsUnit)
    {
        if (empty($priceRule)) {
            return [];
        }
        $rules = $priceRule->groupBy('price_rule_id');
        $reData = [];
        foreach ($rules as $key => $value) {
            $rule = new \stdClass();
            $rule->rule_type = isset(self::$priceRuleArrForDisplay[(int)$key]['show_name'])
                ? self::$priceRuleArrForDisplay[(int)$key]['show_name']
                : "";

            $valueArr = $value->toArray();
            array_walk($valueArr, function (&$item) use ($key, $goodsUnit) {
                unset($item['goods_id']);
                unset($item['price_rule_id']);
                $item['preferential'] = self::getPreferentialText($key, $goodsUnit, $item['preferential']);
            });

            $rule->rules = $valueArr;

            $reData[] = $rule;
        }

        return $reData;
    }

    /**
     * 返回价格体系,优惠值和单位分开的形式
     *
     * @param $priceRule      Collection
     *
     * @param $goodsUnit      string 商品单位
     *
     * @return array
     *
     * [
     *  {
     *  "price_rule_id":1,
     *  "rules":[
     *           {
     *             "buy_num":10,
     *             "preferential_value":200,
     *             "preferential_unit":"元"
     *           },
     *          {...}
     *      ]
     *  }
     * ]
     */
    public static function priceRuleSplitValueUnitHandler($priceRule, $goodsUnit)
    {
        if (empty($priceRule)) {
            return [];
        }
        $rules = $priceRule->groupBy('price_rule_id');
        $reData = [];
        foreach ($rules as $priceRuleId => $value) {
            $rule = new \stdClass();
            $rule->price_rule_id = $priceRuleId;
            $valueArr = $value->toArray();
            array_walk($valueArr, function (&$item) use ($priceRuleId, $goodsUnit) {
                unset($item['goods_id']);
                unset($item['price_rule_id']);
                $item['preferential_value'] =
                    self::convertPreferentialValueForShow($priceRuleId, $item['preferential']);
                $item['preferential_unit'] = self::convertPreferentialUnit($priceRuleId, $goodsUnit);
                unset($item['preferential']);
            });

            $rule->rules = $valueArr;

            $reData[] = $rule;
        }

        return $reData;
    }

    /**
     *  价格体系最大优惠
     *
     * @param $priceRule      Collection
     *
     * @param $goodsUnit      string 商品单位，为了返回优惠文本
     *
     * @return array
     */
    public static function maxPreferentialHandler($priceRule, $goodsUnit)
    {
        if (empty($priceRule)) {
            return [];
        }
        $rules = $priceRule->groupBy('price_rule_id');
        $reData = [];
        foreach ($rules as $key => $value) {
            $rule = new \stdClass();
            $rule->rule_type = isset(self::$priceRuleArrForDisplay[(int)$key]['show_name'])
                ? self::$priceRuleArrForDisplay[(int)$key]['show_name']
                : "";

            //取最大优惠的价格体系进行展示
            $valueItem = $value->sortByDesc('preferential')->first();
            $rule->max_preferential = self::getPreferentialText($key, $goodsUnit, $valueItem['preferential']);
            $reData[] = $rule;
        }

        return $reData;
    }

    /**
     * 获取价格体系输出结构
     *
     * @param $priceRules Collection 价格体系MODEL结构
     *
     * @return array
     */
    public static function getPriceRuleStructure($priceRules)
    {
        $priceRuleArr = [];
        if (!empty($priceRules)) {
            foreach ($priceRules as $value) {
                $priceRuleArr[$value->price_rule_id]['price_rule_id'] = $value->price_rule_id;
                $priceRuleArr[$value->price_rule_id]['rules'][] = [
                    'buy_num'      => $value->buy_num,
                    'preferential' => $value->preferential,
                ];
            }
            array_multisort($priceRuleArr);
        }

        return $priceRuleArr;
    }

    /**
     * 获取商品价格体系规则
     *
     * @param     $goodsId     integer 商品id
     * @param     $buyNum      integer 购买数量
     * @param int $priceRuleId 价格体系规则id
     *
     * @return DpPriceRule
     */
    public static function getPriceRulesByGoodsId($goodsId, $buyNum, $priceRuleId = self::BUY_REDUCE)
    {
        //默认取buyNum的地板最大值

        $query = self::where('goods_id', $goodsId)
            ->where('price_rule_id', $priceRuleId)
            ->where('buy_num', '<=', $buyNum)
            ->orderBy('buy_num', 'desc');

        return $query->first();
    }
}
