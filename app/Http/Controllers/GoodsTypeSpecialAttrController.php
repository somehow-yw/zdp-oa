<?php
/**
 * Created by PhpStorm.
 * 商品分类特殊属性.
 * User: fer
 * Date: 2016/9/28
 * Time: 18:51
 */

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Services\Goods\GoodsTypeSpecialAttrService;
use App\Workflows\GoodsTypeSpecialAttrWorkflow;

class GoodsTypeSpecialAttrController extends Controller
{
    /**
     * 商品分类特殊属性添加/修改
     *
     * @param Request                      $request
     * @param GoodsTypeSpecialAttrWorkflow $goodsTypeSpecialAttrWorkflow
     *
     * @return \Illuminate\Http\Response
     */
    public function updateGoodsTypeSpecialAttr(
        Request $request,
        GoodsTypeSpecialAttrWorkflow $goodsTypeSpecialAttrWorkflow
    ) {
        $this->validate(
            $request,
            [
                'type_id'    => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_types,id',
                'attributes' => 'required|array|between:1,10',
            ],
            [
                'type_id.required' => '分类ID必须有',
                'type_id.integer'  => '分类ID应该是一个整型',
                'type_id.min'      => '分类ID不可小于:min',
                'type_id.exists'   => '商品分类不存在',

                'attributes.required' => '分类属性必须有',
                'attributes.array'    => '分类属性应该是一个数组',
                'attributes.between'  => '分类属性个数必须在:min到:max之间',
            ]
        );

        // 验证属性约束内容
        $messages = $this->validationAttributes($request->input('attributes'));
        if (count($messages)) {
            return $this->renderError($messages[0]);
        }

        $reData = $goodsTypeSpecialAttrWorkflow->updateGoodsTypeSpecialAttr(
            $request->input('type_id'),
            $request->input('attributes')
        );

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品分类特殊属性信息列表
     *
     * @param Request                     $request
     * @param GoodsTypeSpecialAttrService $specialAttrService
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsTypeSpecialAttrList(Request $request, GoodsTypeSpecialAttrService $specialAttrService)
    {
        $this->validate(
            $request,
            [
                'type_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_types,id',
            ],
            [
                'type_id.required' => '分类ID必须有',
                'type_id.integer'  => '分类ID应该是一个整型',
                'type_id.min'      => '分类ID不可小于:min',
                'type_id.exists'   => '商品分类不存在',
            ]
        );

        $reDataArr = $specialAttrService->getGoodsTypeSpecialAttrList($request->input('type_id'));

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品分类特殊属性删除
     *
     * @param Request                      $request
     * @param GoodsTypeSpecialAttrWorkflow $goodsTypeSpecialAttrWorkflow
     *
     * @return \Illuminate\Http\Response
     */
    public function delGoodsTypeSpecialAttr(
        Request $request,
        GoodsTypeSpecialAttrWorkflow $goodsTypeSpecialAttrWorkflow
    ) {
        $this->validate(
            $request,
            [
                'attribute_ids' => 'required|string|between:1,500',
            ],
            [
                'attribute_ids.required' => '属性ID串必须有',
                'attribute_ids.integer'  => '属性ID串应该是一个字符串',
                'attribute_ids.between'  => '属性ID串的长度应在:min到:max',
            ]
        );

        $reData = $goodsTypeSpecialAttrWorkflow->delGoodsTypeSpecialAttr($request->input('attribute_ids'));

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 验证属性约束内容
     *
     * @param $attributesArr
     *
     * @return mixed
     */
    private function validationAttributes($attributesArr)
    {
        foreach ($attributesArr as $key => $attributes) {
            $keys = $key + 1;
            $attributesValidator = Validator::make(
                $attributes,
                [
                    'attribute_id'   => 'required|integer|min:0',
                    'attribute_name' => 'required|string|between:1,11',
                    'format_type_id' => 'required|integer|between:1,20',
                    'must'           => 'required|boolean',
                    'format_values'  => 'required|array|between:1,10',
                ],
                [
                    'attribute_id.required' => $keys . '-属性ID必须有',
                    'attribute_id.integer'  => $keys . '-属性ID必须是一个整型',
                    'attribute_id.min'      => $keys . '-属性ID值不可小于:min',

                    'attribute_name.required' => $keys . '-属性名称必须有',
                    'attribute_name.integer'  => $keys . '-属性名称必须是一个字符串',
                    'attribute_name.between'  => $keys . '-属性名称长度必须在:min, 到:max之间',

                    'format_type_id.required' => $keys . '-属性选择类型ID必须有',
                    'format_type_id.integer'  => $keys . '-属性选择类型必须是一个整型',
                    'format_type_id.between'  => $keys . '-属性选择类型必须是:min, 到:max的整数',

                    'must.required' => $keys . '-是否为必填属性没有选择',
                    'must.boolean'  => $keys . '-是否必填属性必须是一个布尔值',

                    'format_values.required' => $keys . '-属性可能值的约束规则必须有',
                    'format_values.array'    => $keys . '-属性可能值的约束规则必须是一个数组',
                    'format_values.between'  => $keys . '-属性可能值个数必须是:min到:max个',
                ]
            );
            $errors = $attributesValidator->errors();
            if (!$errors->isEmpty()) {
                $message = current(current($errors));

                return $message;
            }

            foreach ($attributes['format_values'] as $values) {
                $valueArr = ['format_values' => $values];
                $valueTxt = 'value,unit,default,rule';
                /** @see Illuminate\Validation\Factory */
                $attributesValueValidator = Validator::make(
                    $valueArr,
                    [
                        'format_values' => "array|size:4|arr_has_key:{$valueTxt}",
                    ],
                    [
                        'format_values.array' => '属性的值必须是一个数组',
                        'format_values.size'  => '属性的值必须包括:size个元素',
                    ]
                );
                $errors = $attributesValueValidator->errors();
                if (!$errors->isEmpty()) {
                    $message = current(current($errors));

                    return $message;
                }
            }
        }
    }
}
