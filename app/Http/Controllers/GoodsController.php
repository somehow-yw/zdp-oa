<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Exceptions\Goods\GoodsSpecConstraintsException;
use App\Exceptions\Goods\GoodsSpecialAttributesException;
use App\Http\Controllers\RequestTraits\GoodsAddRequest;

use App\Models\DpGoodsConstraints;
use App\Models\DpGoodsTypeSpecialAttribute;
use App\Services\Goods\GoodsService;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Validator;
use Zdp\Main\Data\Services\Goods\SameGoodsService;

class GoodsController extends Controller
{
    use GoodsAddRequest;

    /**
     * 添加(转移)商品
     *
     * @param Request      $request
     * @param GoodsService $goodsService
     *
     * @return \Illuminate\Http\Response
     * @throws AppException
     */
    public function addGoods(Request $request, GoodsService $goodsService)
    {
        $this->goodsIdUnique($request);
        $this->validateGoods($request);
        $this->parseConstraintsThenValidate($request);
        $adminId = $request->user()->id;
        $goodsService->addGoods(
            $request->input('basic_infos'),
            $request->input('basic_attributes'),
            $request->input('special_attributes'),
            $request->input('pictures'),
            $request->input('inspection_reports', []),
            $adminId
        );
        $reData = [
            'data'    => [],
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品信息更新
     *
     * @param Request      $request
     * @param GoodsService $goodsService
     *
     * @return \Illuminate\Http\Response
     * @throws AppException
     */
    public function updateGoodsInfo(
        Request $request,
        GoodsService $goodsService
    ) {
        $this->validate(
            $request,
            [
                'basic_infos.goods_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'basic_infos.goods_id.required' => '商品id不能为空',
                'basic_infos.goods_id.integer'  => '商品id必须是整数',
                'basic_infos.goods_id.exists'   => '商品id不存在',
            ]
        );
        $this->validateGoods($request);
        $this->parseConstraintsThenValidate($request);
        $adminInfoObj = $request->user();
        $adminId = $adminInfoObj->id;
        $adminTel = $adminInfoObj->login_name;
        $goodsService->updateGoodsInfo(
            $request->input('basic_infos'),
            $request->input('basic_attributes'),
            $request->input('special_attributes'),
            $request->input('pictures'),
            $request->input('inspection_reports', []),
            $request->input('price_rules', []),
            $adminId,
            $adminTel
        );
        $reData = [
            'data'    => [],
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 旧商品图片(包括检验报告)的获取
     *
     * @param Request      $request
     * @param GoodsService $goodsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getOldGoodsPicture(
        Request $request,
        GoodsService $goodsService
    ) {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是一个整形',
                'goods_id.min'      => '商品id不能小于:min',
                'goods_id.exists'   => '商品不存在',
            ]
        );
        $rePictureArr =
            $goodsService->getOldGoodsPicture($request->input('goods_id'));
        $reData = [
            'data'    => $rePictureArr,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 店铺信息获取
     *
     * @param Request     $request
     * @param ShopService $shopService
     *
     * @return \Illuminate\Http\Response
     */
    public function getShopInfo(Request $request, ShopService $shopService)
    {
        $this->validate(
            $request,
            [
                'shop_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_shopInfo,shopId',
            ],
            [
                'shop_id.required' => '店铺id不能为空',
                'shop_id.integer'  => '店铺id必须是一个整形',
                'shop_id.min'      => '店铺id不能小于:min',
                'shop_id.exists'   => '店铺不存在',
            ]
        );
        $reShopInfoArr =
            $shopService->getShopInfoFromGoodsList($request->input('shop_id'));
        $reData = [
            'data'    => $reShopInfoArr,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     *  商品详情查询
     *
     * @param Request      $request
     * @param GoodsService $goodsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsInfo(Request $request, GoodsService $goodsService)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是一个整形',
                'goods_id.min'      => '商品id不能小于:min',
                'goods_id.exists'   => '商品不存在',
            ]
        );
        $reGoodsInfoArr =
            $goodsService->getGoodsInfo($request->input('goods_id'));
        $reData = [
            'data'    => $reGoodsInfoArr,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    public function getSameGoods(
        Request $request,
        GoodsService $goodsService,
        SameGoodsService $service
    ) {
        $this->validate(
            $request,
            [
                'goods_id'               => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
                'type_id'                => 'required|integer',
                'brand'                  => 'required|string',
                // 规格约束值
                'guige.format_type_id'   => 'required|integer',
                'guige.values'           => 'required|array',
                // 型号约束值
                'xinghao.format_type_id' => 'required|integer',
                'xinghao.values'         => 'required|array',
                // 单位
                'unit'                   => 'required|string',
            ]
        );

        $guige = $goodsService->attrArrToText(
            $request->input('guige.values'),
            $request->input('guige.format_type_id')
        );

        $xinghao = $goodsService->attrArrToText(
            $request->input('xinghao.values'),
            $request->input('xinghao.format_type_id')
        );

        return $this->render(
            'goods.list',
            $service->sameGoodsByShop(
                $request->input('goods_id'),
                $request->input('type_id'),
                $request->input('brand'),
                $xinghao,
                $guige,
                $request->input('unit')
            ),
            '',
            0
        );
    }

    /**
     * 迭代规格约束、型号约束、特殊约束并校验
     *
     * @param $request Request 所有合法的请求
     *
     * @throws AppException
     */
    private function parseConstraintsThenValidate($request)
    {
        /** @var integer $goodsTypeId 商品类型id */

        $goodsTypeId = $request->input('basic_infos.goods_type_id');

        /** @var integer $specConstraintId 规格约束id */

        $specConstraintId =
            $request->input('basic_attributes.specs.constraint_id');

        /** @var array $specConstraintValues 规格约束值 */

        $specConstraintValues =
            $request->input('basic_attributes.specs.values');

        /**
         * 校验规格约束值
         */
        $specConstraint = DpGoodsConstraints::find($specConstraintId);
        try {
            $this->validateConstraint($specConstraint, $specConstraintValues);
        } catch (AppException $e) {
            throw new AppException(
                "规格约束错误," . $e->getMessage()
            );
        }

        /** @var integer $typeConstraintId 型号约束id */

        $typeConstraintId =
            $request->input('basic_attributes.types.constraint_id');

        /** @var array $typeConstraintValues 类型约束值 */

        $typeConstraintValues =
            $request->input('basic_attributes.types.values');
        /**
         * 校验类型约束值
         */
        $typeConstraint = DpGoodsConstraints::find($typeConstraintId);
        try {
            $this->validateConstraint($typeConstraint, $typeConstraintValues);
        } catch (AppException $e) {
            throw new AppException(
                "型号约束错误," . $e->getMessage()
            );
        }

        /**
         * @var array $specialAttributes
         * [
         *   {
         *       "constraint_id":5,
         *       "must":false,
         *       "name":"带皮",
         *       "values":[
         *       {
         *         "value":"是",
         *         "unit":""
         *       },
         *       {...}
         *       ]
         *   },
         *  {...}
         * ]
         * 特殊属性值
         */
        $specialAttributes = $request->input('special_attributes', []);

        /**
         * 校验特殊属性
         */
        $this->validateSpecialAttr($goodsTypeId, $specialAttributes);
    }

    /**
     * 验证属性是否满足约束
     *
     * @param $constraint            DpGoodsConstraints|DpGoodsTypeSpecialAttribute
     *                               商品基础属性约束表记录id
     * @param $values                array 需要校验的值
     *                               "values": [
     *                               {
     *                               "value": "6M",
     *                               "unit": ""
     *                               },
     *                               {
     *                               "value": "6M",
     *                               "unit": ""
     *                               }
     *                               ]
     */
    private function validateConstraint($constraint, array $values)
    {
        $formatTypeId = $constraint->format_type_id;
        //将json数组中的value列抽成一个数组方便单选多选校验所选值是否在数组中
        $formatValues = json_decode($constraint->format_values, true);
        $valuesCanSelect = array_column($formatValues, 'value');
        $rulesShouldMatch = array_column($formatValues, 'rule');
        $values = array_column($values, 'value');

        switch ($formatTypeId) {
            //文本框
            case 1:
                $this->validateText($values, $rulesShouldMatch);
                break;
            //单选
            case 2:
                $this->validateRadio($values, $valuesCanSelect);
                break;
            //多选
            case 3:
                $this->validateCheckbox($values, $valuesCanSelect);
                break;
            //X-Y区间
            case 4:
                $this->validateXY($values, $rulesShouldMatch);
                break;
            //X*Y值
            case 5:
                $this->validateXY($values, $rulesShouldMatch);
                break;
        }
    }

    /**
     * 验证商品特殊属性
     *
     * @param $goodsTypeId       integer 商品类型id
     * @param $specialAttributes array 特殊属性约束值
     *
     * @throws AppException
     */
    private function validateSpecialAttr($goodsTypeId, $specialAttributes)
    {
        $constraintIds = array_column($specialAttributes, "constraint_id");
        $mustSpecialAttrs =
            DpGoodsTypeSpecialAttribute::where("type_id", $goodsTypeId)
                                       ->where('must', 1)->get();
        foreach ($mustSpecialAttrs as $mustSpecialAttr) {
            if (!in_array($mustSpecialAttr->id, $constraintIds)) {
                throw new GoodsSpecialAttributesException(
                    "特殊属性必填项:{$mustSpecialAttr->attribute_name}不能为空",
                    GoodsSpecialAttributesException::GOODS_MUST_SPECIAL_ATTRIBUTES_CAN_NOT_BE_BLANK
                );
            }
        }

        foreach ($specialAttributes as $specialAttribute) {
            /** @var DpGoodsTypeSpecialAttribute $specialConstraint */
            $specialConstraint =
                DpGoodsTypeSpecialAttribute::find($specialAttribute['constraint_id']);
            //校验特殊属性名称是否与数据库相符,防止存进错误的数据
            if ($specialConstraint->attribute_name !=
                $specialAttribute['name']
            ) {
                throw new GoodsSpecialAttributesException(
                    "该特殊属性的名字应该为{$specialConstraint->attribute_name}",
                    GoodsSpecialAttributesException::GOODS_SPECIAL_ATTRIBUTES_NAME_NOT_MATCH
                );
            }
            //校验特殊属性值和单位
            try {
                $this->validateConstraint($specialConstraint,
                    $specialAttribute['values']);
            } catch (AppException $e) {
                throw new AppException(
                    "特殊属性约束:{$specialAttribute['name']}错误," . $e->getMessage()
                );
            }
        }
    }

    /**
     * 验证文本框
     *
     * @param array $values 需要被验证的格式值
     * @param array $rules  规则 可选 "string" "integer" "numeric"
     *
     * @throws AppException
     */
    private function validateText(array $values, array $rules)
    {
        //对于文本框 有且只能有一个值
        if (1 !== count($values)) {
            throw new AppException("对于文本框只能有一个值",
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }
        $validator = $this->makeValidator($values[0], $rules[0]);

        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            throw new AppException($msg,
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }
    }

    /**
     * 验证单选框
     *
     * @param array $values           被验证的格式值
     * @param array $valuesCanSelects 可以选择的值
     *
     * @throws AppException
     */
    private function validateRadio(array $values, array $valuesCanSelects)
    {
        //对于单选框 有且只能有一个值
        if (1 !== count($values)) {
            throw new AppException("对于单选框有且只能有一个值",
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }
        $in_checkboxes = in_array($values[0], $valuesCanSelects);
        if (!$in_checkboxes) {
            throw new AppException("该单选选项不存在",
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }
    }

    /**
     * 验证多选框
     *
     * @param array $values          被验证的格式值
     * @param array $canSelectValues 可以选择的值
     *
     * @throws AppException
     */
    private function validateCheckbox(array $values, array $canSelectValues)
    {
        //多选框 至少得选择一个
        if (1 > count($values)) {
            throw new AppException("多选框至少得选择一项",
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }
        //去重
        $values = array_unique($values);
        foreach ($values as $value) {
            if (!in_array($value, $canSelectValues)) {
                throw new AppException(
                    "{$value}不在多选选项中",
                    GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT
                );
            }
        }
    }

    /**
     * 验证X-Y类型的
     *
     * @param array $values           可以验证的值
     * @param array $rulesShouldMatch 应该匹配的规则
     *
     * @throws AppException
     */
    private function validateXY(array $values, array $rulesShouldMatch)
    {
        //X Y 型的有且只有两个参数
        if (2 !== count($values)) {
            throw new AppException("XY型的值必须为两个",
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }

        $validatorX = $this->makeValidator($values[0], $rulesShouldMatch[0]);
        $validatorY = $this->makeValidator($values[1], $rulesShouldMatch[1]);
        if ($validatorX->fails()) {
            $message = $validatorX->errors()->first();
            throw new AppException($message,
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }

        if ($validatorY->fails()) {
            $message = $validatorY->errors()->first();
            throw new AppException($message,
                GoodsSpecConstraintsException::GOODS_ATTR_NOT_MATCH_CONSTRAINT);
        }
    }

    /**
     * 按照验证规则生成验证器
     *
     * @param  $value  string 被验证的值
     * @param  $rule   string 规则 可选 "string" "integer" "numeric"
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function makeValidator($value, $rule)
    {
        $messageMap =
            [
                "string"         => "约束值必须为字符串",
                "integer"        => "约束值必须为整数",
                "numeric"        => "约束值必须为数字",
                "value.required" => "约束的值不能为空",
            ];
        $validator = Validator::make(
            [
                'value' => $value,
            ],
            [
                'value' => "required|$rule",
            ],
            [
                "value.required" => $messageMap["value.required"],
                "value.$rule"    => $messageMap[$rule],
            ]
        );

        return $validator;
    }
}
