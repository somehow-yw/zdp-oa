<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/14/16
 * Time: 1:32 PM
 */

namespace App\Http\Controllers\RequestTraits;

use App\Exceptions\AppException;

use App\Exceptions\Goods\AddGoodsException;
use App\Exceptions\Goods\GoodsSpecialAttributesException;
use App\Models\DpPriceRule;
use Illuminate\Http\Request;
use Validator;
use Zdp\Main\Data\Models\DpGoodsBasicAttribute;

trait GoodsAddRequest
{
    public function goodsIdUnique(Request $request)
    {
        $this->validate(
            $request,
            [
                'basic_infos.goods_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id|
                unique:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'basic_infos.goods_id.required' => '商品id不能为空',
                'basic_infos.goods_id.integer'  => '商品id必须是整数',
                'basic_infos.goods_id.exists'   => '商品id不存在',
                'basic_infos.goods_id.unique'   => '商品id已经在新表存在',
            ]
        );
    }

    public function validateGoods(Request $request)
    {
        $minPrice = DpGoodsBasicAttribute::GOODS_MIN_PRICE;
        $this->validate(
            $request,
            [
                //商品基本信息
                'basic_infos.goods_name'                  => 'required|string|min:1',
                'basic_infos.goods_type_id'               => 'required|integer|
                exists:mysql_zdp_main.dp_goods_types,id',
                'basic_infos.brand_id'                    => "required|integer|
                exists:mysql_zdp_main.dp_brands,id",
                'basic_infos.origin'                      => 'required|string',
                'basic_infos.halal'                       => 'required|boolean',
                'basic_infos.smuggle_id'                  => 'required|integer|min:1',
                'basic_infos.goods_title'                 => 'required|string|between:15,25',
                'basic_infos.describe'                    => 'string|between:1,255',
                //商品基础属性
                'basic_attributes.goods_price'            => 'required|numeric|greater_than:' . $minPrice,
                'basic_attributes.goods_unit_id'          => 'required|integer|min:0',
                'basic_attributes.price_adjust_frequency' => 'required|integer|in:0,1',
                'basic_attributes.rough_weight'           => 'required|numeric|min:0',
                'basic_attributes.net_weight'             => 'required|numeric|min:0',
                'basic_attributes.meat_weight'            => 'numeric|min:0',
                'basic_attributes.inventory'              => 'required|integer|min:0',
                'basic_attributes.minimum_order_quantity' => 'required|integer|min:0',
                //规格约束值
                'basic_attributes.specs.constraint_id'    => 'required|integer|
                exists:mysql_zdp_main.dp_goods_constraints,id',
                'basic_attributes.specs.must'             => 'required|boolean',
                'basic_attributes.specs.name'             => 'required|string|in:规格',
                'basic_attributes.specs.values'           => 'required|array',
                //类型约束值
                'basic_attributes.types.constraint_id'    => 'required|integer|
                exists:mysql_zdp_main.dp_goods_constraints,id',
                'basic_attributes.types.must'             => 'required|boolean',
                'basic_attributes.types.name'             => 'required|string|in:型号',
                'basic_attributes.types.values'           => 'required|array',
                //特殊属性约束
                'special_attributes'                      => 'array',
                //图片
                'pictures'                                => 'required|array',
                //检验报告
                'inspection_reports'                      => 'array',
                //价格体系
                'price_rules'                             => 'array',
            ],
            [
                'basic_infos.goods_name.required' => '商品名称不能为空',
                'basic_infos.goods_name.string'   => '商品名称必须是字符串',
                'basic_infos.goods_name.min'      => '商品名称最少为:min个字符',

                'basic_infos.goods_type_id.required' => '商品类型id不能为空',
                'basic_infos.goods_type_id.integer'  => '商品类型id必须为整数',
                'basic_infos.goods_type_id.exists'   => '商品类型id不存在',

                'basic_infos.brand_id.required' => '商品品牌id不能为空',
                'basic_infos.brand_id.integer'  => '商品品牌id必须为整数',
                'basic_infos.brand_id.exists'   => '商品品牌id不存在',

                'basic_infos.origin.required' => '商品产地不能为空',
                'basic_infos.origin.string'   => '商品产地必须是字符串',

                'basic_infos.halal.required' => '商品是否清真不能为空',
                'basic_infos.halal.boolean'  => '商品是否清真应该是个布尔值',

                'basic_infos.smuggle_id.required' => '商品国别id不能为空',
                'basic_infos.smuggle_id.integer'  => '商品国别id必须是个整数',
                'basic_infos.smuggle_id.min'      => '商品国别id最小为:min',

                'basic_infos.goods_title.required' => '商品标题不能为空',
                'basic_infos.goods_title.string'   => '商品标题必须是字符串',
                'basic_infos.goods_title.between'  => '商品标题应该是:min到:max个字符',

                //'basic_infos.describe.required' => '商品描述不能为空',
                'basic_infos.describe.string'      => '商品描述必须是字符串',
                'basic_infos.describe.between'     => '商品描述应该是:min到:max个字符',

                'basic_attributes.goods_price.required'     => '商品价格不能为空',
                'basic_attributes.goods_price.numeric'      => '商品价格必须是个数字',
                'basic_attributes.goods_price.greater_than' => '商品单价小于等于' . $minPrice . '元，请重新设置单价',

                'basic_attributes.goods_unit_id.required' => '商品单位id不能为空',
                'basic_attributes.goods_unit_id.numeric'  => '商品单位id必须是个数字',
                'basic_attributes.goods_unit_id.min'      => '商品单位id不能小于:min',

                'basic_attributes.price_adjust_frequency.required' => '商品价格过期频率不能为空',
                'basic_attributes.price_adjust_frequency.integer'  => '商品价格过期频率必须是个整数',
                'basic_attributes.price_adjust_frequency.in'       => '商品价格过期频率只能是0或1',

                'basic_attributes.rough_weight.required' => '商品毛重不能为空',
                'basic_attributes.rough_weight.numeric'  => '商品毛重必须是个数字',
                'basic_attributes.rough_weight.min'      => '商品毛重不能小于:min',

                'basic_attributes.net_weight.required' => '商品净重不能为空',
                'basic_attributes.net_weight.numeric'  => '商品净重必须是个数字',
                'basic_attributes.net_weight.min'      => '商品净重不能小于:min',

                'basic_attributes.meat_weight.numeric' => '商品解冻后重量必须是个数字',
                'basic_attributes.meat_weight.min'     => '商品解冻后重量不能小于:min',


                'basic_attributes.inventory.required' => '商品库存不能为空',
                'basic_attributes.inventory.integer'  => '商品库存必须是个整数',
                'basic_attributes.inventory.min'      => '商品库存不能小于:min',

                'basic_attributes.minimum_order_quantity.required' => '商品最小起购量不能为空',
                'basic_attributes.minimum_order_quantity.integer'  => '商品最小起购量必须是个整数',
                'basic_attributes.minimum_order_quantity.min'      => '商品最小起购量不能小于:min',

                'basic_attributes.specs.constraint_id.required' => '商品规格约束id不能为空',
                'basic_attributes.specs.constraint_id.integer'  => '商品规格约束id必须是整数',
                'basic_attributes.specs.constraint_id.exists'   => '商品规格约束id不存在',

                'basic_attributes.specs.must.required' => '商品规格约束是否必填不能为空',
                'basic_attributes.specs.must.boolean'  => '商品规格约束是否必填必须是个布尔值',
                'basic_attributes.specs.must.exists'   => '商品规格约束是否必填',

                'basic_attributes.specs.name.required' => '商品规格约束名称不能为空',
                'basic_attributes.specs.name.string'   => '商品规格约束名称必须是字符串',
                'basic_attributes.specs.name.in'       => '商品规格约束名称必须为:规格',

                'basic_attributes.specs.values.required' => '商品规格约束值不能为空',
                'basic_attributes.specs.values.array'    => '商品规格约束值必须是个数组',

                'basic_attributes.types.constraint_id.required' => '商品型号约束id不能为空',
                'basic_attributes.types.constraint_id.integer'  => '商品型号约束id必须是整数',
                'basic_attributes.types.constraint_id.exists'   => '商品型号约束id不存在',

                'basic_attributes.types.must.required' => '商品型号约束是否必填不能为空',
                'basic_attributes.types.must.boolean'  => '商品型号约束是否必填必须是个布尔值',
                'basic_attributes.types.must.exists'   => '商品型号约束是否必填',

                'basic_attributes.types.name.required' => '商品型号约束名称不能为空',
                'basic_attributes.types.name.string'   => '商品型号约束名称必须是字符串',
                'basic_attributes.types.name.in'       => '商品型号约束名称必须为:型号',

                'basic_attributes.types.values.required' => '商品型号约束值不能为空',
                'basic_attributes.types.values.array'    => '商品型号约束值必须是个数组',

                'special_attributes.array' => '商品特殊属性约束值必须为数组',

                'pictures.required' => '商品图片必须有',
                'pictures.array'    => '商品图片必须为数组',

                'inspection_reports.array' => '检验报告图片必须为数组',

                'price_rules.array' => '价格体系必须是个数组',
            ]
        );
        if ($request->has('special_attributes')) {
            $this->validateSpecialAttrStructure($request->input('special_attributes'));
        }
        $this->validatePicturesStructure($request->input('pictures'));

        if ($request->has('inspection_reports')) {
            $this->validateInspectionReportsStructure($request->input('inspection_reports'));
        }

        if ($request->has('price_rules')) {
            $this->validatePriceRuleStructure(
                $request->input('price_rules'),
                $request->input('basic_attributes.goods_price')
            );
        }
    }

    /**
     *
     * @param array $priceRules
     * @param       $price double 商品单价
     *
     *"price_rules":[
     *     {
     *          "price_rule_id":1,
     *          "rules":[
     *              {
     *                  "buy_num":10,
     *                  "preferential_value":200
     *              }
     *          ]
     *     }
     *   ]
     * @throws AddGoodsException
     * @throws AppException
     */
    private function validatePriceRuleStructure(array $priceRules, $price)
    {
        $priceRuleIndex = 1;
        foreach ($priceRules as $priceRule) {
            /** @var \Illuminate\Contracts\Validation\Validator $priceRuleValidator */
            $priceRuleValidator = Validator::make(
                [
                    'price_rule' => $priceRule,
                ],
                [
                    'price_rule.price_rule_id' => 'required|integer|in:1,2',
                    'price_rule.rules'         => 'required|array',
                ],
                [
                    'price_rule.price_rule_id.required' => "第{$priceRuleIndex}个价格体系的id不能为空",
                    'price_rule.price_rule_id.integer'  => "第{$priceRuleIndex}个价格体系的id必须是个整数",
                    'price_rule.price_rule_id.in'       => "第{$priceRuleIndex}个价格体系的id只能是1或者2",

                    'price_rule.rules.required' => "第{$priceRuleIndex}个价格体系的规则不能为空",
                    'price_rule.rules.array'    => "第{$priceRuleIndex}个价格体系的规则必须是个数组",
                ]
            );
            if ($priceRuleValidator->fails()) {
                $errorMsg = $priceRuleValidator->errors()->first();
                throw new AddGoodsException(
                    $errorMsg
                );
            }
            $ruleIndex = 1;
            $buyNumArr = [];
            foreach ($priceRule['rules'] as $rule) {
                /** @var \Illuminate\Contracts\Validation\Validator $ruleValidator */
                $ruleValidator = Validator::make(
                    [
                        'rule' => $rule,
                    ],
                    [
                        'rule.buy_num'            => 'required|integer',
                        'rule.preferential_value' => 'required|numeric',
                    ],
                    [
                        'rule.buy_num.required' => "第{$priceRuleIndex}个价格体系的第{$ruleIndex}个规则购买数量不能为空",
                        'rule.buy_num.integer'  => "第{$priceRuleIndex}个价格体系的第{$ruleIndex}个规则购买数量必须是个整数",

                        'rule.preferential_value.required' => "第{$priceRuleIndex}个价格体系的第{$ruleIndex}个规则优惠不能为空",
                        'rule.preferential_value.numeric'  => "第{$priceRuleIndex}个价格体系的第{$ruleIndex}个规则优惠必须是个数字",
                    ]
                );
                if ($ruleValidator->fails()) {
                    $errorMsg = $ruleValidator->errors()->first();
                    throw new AddGoodsException(
                        $errorMsg
                    );
                }
                if (in_array($rule['buy_num'], $buyNumArr)) {
                    throw  new AppException("价格体系规则中购买数量不能重复");
                } else {
                    $buyNumArr[] = $rule['buy_num'];
                }

                /**
                 * 买减的时候校验减免金额是否大于支出 例如单价30,买2件-5元，即55元 合法
                 * 单价5元 买2减15 10-15<=0 非法
                 */
                if ($priceRule['price_rule_id'] == DpPriceRule::BUY_REDUCE) {
                    $buyPrice = $rule['buy_num'] * (double)$price;
                    $reducePrice = $rule['preferential_value'];
                    if ($reducePrice >= $buyPrice) {
                        $errorMsg = "买减价格体系的第{$ruleIndex}个规则优惠金额大于购买金额 {$reducePrice} > {$rule['buy_num']} * {$price}";
                        throw new AppException($errorMsg);
                    }
                }
                $ruleIndex++;
            }
            $priceRuleIndex++;
        }
    }

    /**
     * 验证特殊属性参数是否合法
     *
     * @param array $specialAttr 特殊属性
     *
     * @throws GoodsSpecialAttributesException
     */
    private function validateSpecialAttrStructure(array $specialAttr)
    {
        $index = 1;
        foreach ($specialAttr as $value) {
            /** @var \Illuminate\Contracts\Validation\Validator $specialAttrValidator */
            $specialAttrValidator = Validator::make(
                [
                    'special_attribute' => $value,
                ],
                [
                    'special_attribute.constraint_id' => 'required|integer|
                    exists:mysql_zdp_main.dp_goods_type_special_attributes,id',
                    'special_attribute.must'          => 'required|boolean|',
                    'special_attribute.name'          => 'required|string|min:1',
                    'special_attribute.values'        => 'required|array',
                ],
                [
                    'special_attribute.constraint_id.required' => "第{$index}个特殊属性约束id不能为空",
                    'special_attribute.constraint_id.integer'  => "第{$index}个特殊属性约束id必须是个整数",
                    'special_attribute.constraint_id.exists'   => "第{$index}个特殊属性约束id不存在",

                    'special_attribute.must.required' => "第{$index}个特殊属性是否必填不能为空",
                    'special_attribute.must.boolean'  => "第{$index}个特殊属性是否必填是个布尔值",

                    'special_attribute.name.required' => "第{$index}个特殊属性名称不能为空",
                    'special_attribute.name.boolean'  => "第{$index}个特殊属性名称必须是个布尔值",
                    'special_attribute.name.min'      => "第{$index}个特殊属性名称最少为:min个字符",

                    'special_attribute.values.required' => "第{$index}个特殊属性值不能为空",
                    'special_attribute.values.array'    => "第{$index}个特殊属性值必须为数组",
                ]
            );
            if ($specialAttrValidator->fails()) {
                $message = $specialAttrValidator->errors()->first();
                throw new GoodsSpecialAttributesException(
                    $message,
                    GoodsSpecialAttributesException::GOODS_SPECIAL_ATTRIBUTES_INVALID
                );
            }
            $index++;
        }
    }

    /**
     * 验证商品图片参数是否合法
     *
     * @param array $pictures 图片
     *
     * @throws AddGoodsException
     */
    private function validatePicturesStructure(array $pictures)
    {
        /**
         * @var array $picture
         * [
         * "picture_id" => 9
         * "picture_add" => "Public/Uploads/goods/20161013/487727.jpg"
         * "sort_value" => 1
         * ]
         */
        $index = 1;
        foreach ($pictures as $picture) {
            /** @var \Illuminate\Contracts\Validation\Validator $pictureValidator */
            $pictureValidator = Validator::make(
                [
                    'picture' => $picture,
                ],
                [
                    'picture.picture_id'  => 'integer|min:0',
                    'picture.picture_add' => 'required|string|between:1,255',
                    'picture.sort_value'  => 'required|integer|min:1',
                ],
                [
                    'picture.picture_id.integer' => "第{$index}幅图片id必须是个整数",
                    'picture.picture_id.min'     => "第{$index}幅图片id最少为:min",

                    'picture.picture_add.required' => "第{$index}幅图片路径不能为空",
                    'picture.picture_add.string'   => "第{$index}幅图片路径必须是字符串",
                    'picture.picture_add.between'  => "第{$index}幅图片路径长度应该在:min到:max",

                    'picture.sort_value.required' => "第{$index}幅图片排序值不能为空",
                    'picture.sort_value.integer'  => "第{$index}幅图片排序值必须是个整数",
                    'picture.sort_value.min'      => "第{$index}幅图片排序值最小为:min",
                ]
            );
            if ($pictureValidator->fails()) {
                $message = $pictureValidator->errors()->first();
                throw new AddGoodsException(
                    $message,
                    AddGoodsException::GOODS_PICTURES_INVALID
                );
            }
            $index++;
        }
        if (3 > count($pictures)) {
            throw  new AddGoodsException("商品的图片至少有3张", AddGoodsException::GOODS_PICTURES_INVALID);
        }
    }

    /**
     * 验证检验报告数组是否合法
     *
     * @param array $inspections 检验报告图片
     *
     * @throws AppException
     */
    private function validateInspectionReportsStructure(array $inspections)
    {
        /**
         * @var array $picture
         * [
         * "picture_id" => 9
         * "picture_add" => "Public/Uploads/goods/20161013/487727.jpg"
         * "sort_value" => 1
         * ]
         */
        $index = 1;
        foreach ($inspections as $inspection) {
            /** @var \Illuminate\Contracts\Validation\Validator $pictureValidator */
            $inspectionValidator = Validator::make(
                [
                    'picture' => $inspection,
                ],
                [
                    'picture.picture_id'  => 'integer|min:0',
                    'picture.picture_add' => 'required|string|between:1,255',
                    'picture.sort_value'  => 'required|integer|min:1',
                ],
                [
                    'picture.picture_id.integer' => "检验报告第{$index}幅图片id必须是个整数",
                    'picture.picture_id.min'     => "检验报告第{$index}幅图片id最少为:min",

                    'picture.picture_add.required' => "检验报告第{$index}幅图片路径不能为空",
                    'picture.picture_add.string'   => "检验报告第{$index}幅图片路径必须是字符串",
                    'picture.picture_add.between'  => "检验报告第{$index}幅图片路径长度应该在:min到:max",

                    'picture.sort_value.required' => "检验报告第{$index}幅图片排序值不能为空",
                    'picture.sort_value.integer'  => "检验报告第{$index}幅图片排序值必须是个整数",
                    'picture.sort_value.min'      => "检验报告第{$index}幅图片排序值最小为:min",
                ]
            );
            if ($inspectionValidator->fails()) {
                $message = $inspectionValidator->errors()->first();
                throw new AddGoodsException(
                    $message,
                    AddGoodsException::GOODS_INSPECTIONS_REPORT_INVALID
                );
            }
            $index++;
        }
        if (count($inspections) > 3) {
            throw  new AddGoodsException("商品检验报告最多有3张", AddGoodsException::GOODS_INSPECTIONS_REPORT_INVALID);
        }
    }
}