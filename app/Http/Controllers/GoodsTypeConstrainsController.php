<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\Services\Goods\GoodsConstraintsService;
use App\Exceptions\Goods\GoodsSpecConstraintsException;

class GoodsTypeConstrainsController extends Controller
{
    /**
     * 获取商品分类基本属性
     *
     * @param Request                 $request
     * @param GoodsConstraintsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsBasicAttr(Request $request, GoodsConstraintsService $service)
    {
        $this->validate(
            $request,
            [
                'type_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_types,id',
            ],
            [
                'type_id.required' => '商品类型id不能为空',
                'type_id.integer'  => '商品类型id必须是一个整形',
                'type_id.exists'   => '商品类型id不存在',
            ]
        );

        $data = $service->getGoodsBasicAttr($request->input('type_id'));
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 添加或者修改商品基本属性
     *
     * @param Request                 $request
     * @param GoodsConstraintsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateGoodsBasicAttr(Request $request, GoodsConstraintsService $service)
    {
        //基本校验
        $this->validate(
            $request,
            [
                'type_id'                        => 'required|integer|exists:mysql_zdp_main.dp_goods_types,id',
                'type_constraint.format_type_id' => 'required|integer|min:1',
                'type_constraint.format_values'  => 'required',
                'spec_constraint.format_type_id' => 'required|integer|min:1',
                'spec_constraint.format_values'  => 'required',
            ],
            [
                'type_id.required' => '商品类型id不能为空',
                'type_id.integer'  => '商品类型id必须是一个整形',
                'type_id.exists'   => '商品类型id不存在',

                'type_constraint.format_type_id.required' => '型号约束类型id不能为空',
                'type_constraint.format_type_id.integer'  => '型号约束类型id必须为整数',
                'type_constraint.format_type_id.min'      => '型号约束类型id不能小于:min',

                'type_constraint.format_values.required' => '型号约束值不能为空',

                'spec_constraint.format_type_id.required' => '规格约束类型id不能为空',
                'spec_constraint.format_type_id.integer'  => '规格约束类型id必须为整数',
                'spec_constraint.format_type_id.min'      => '规格约束类型id不能小于:min',

                'spec_constraint.format_values.required' => '规格约束值不能为空'
            ]
        );

        //验证类型约束值的长度
        $this->validateConstraint(
            $request->input('type_constraint.format_values'),
            $request->input('type_constraint.format_type_id')
        );

        //验证规格约束值的长度
        $this->validateConstraint(
            $request->input('spec_constraint.format_values'),
            $request->input('spec_constraint.format_type_id')
        );

        //分别验证品牌类型约束和规格约束的format_values数组
        $this->validateFormatValues($request->input('type_constraint.format_values'));
        $this->validateFormatValues($request->input('spec_constraint.format_values'));

        //创建或更新商品基本属性
        $service->updateGoodsBasicAttr(
            $request->input('type_id'),
            $request->input('type_constraint'),
            $request->input('spec_constraint')
        );

        $reData = [
            'data'    => [],
            'message' => 'OK',
            'code'    => 0
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 验证型号约束或规格约束值
     *
     * @param $formatValues array 约束字段数据
     * @param $formatTypeId integer 约束类型id
     *
     * @throws AppException
     */
    protected function validateConstraint($formatValues, $formatTypeId)
    {
        $formatTypeIds = collect(config('input_format.goods_attribute'))->keyBy('id');
        $validateRule = $formatTypeIds[$formatTypeId]['validate'];
        $formatTypeName = $formatTypeIds[$formatTypeId]['name'];
        /** @var \Illuminate\Contracts\Validation\Validator $validator */
        $validator = Validator::make(
            ['format_values' => $formatValues],
            [
                'format_values' => $validateRule
            ],
            [
                'format_values.size'    => "{$formatTypeName}约束值必须为:size组",
                'format_values.between' => "{$formatTypeName}约束值必须为:min组到:max组",
            ]
        );
        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            throw new AppException($msg, GoodsSpecConstraintsException::GOODS_CONSTRAINT_FORMAT_VALUES_NOT_MATCHED);
        }
    }

    /**
     * 验证格式约束值是否合法
     *
     * @param $formatValues array 格式约束值
     *
     * @throws AppException
     *
     * @return void
     */
    protected function validateFormatValues($formatValues)
    {
        Validator::extend('has_key', function ($attribute, $value, $parameters, $validator) {
            foreach ($parameters as $parameter) {
                if (!array_has($value, $parameter)) {
                    $validator->errors()->add('format_values', "格式约束值的{$parameter}字段必须有");

                    return false;
                }

                if ($parameter == "default" && (!is_bool($value['default']))) {
                    $validator->errors()->add('format_values', "格式约束值的default字段必须为布尔值");

                    return false;
                }
            }

            return true;
        });
        foreach ($formatValues as $formatValue) {
            /** @var \Illuminate\Contracts\Validation\Validator $validator */
            $validator = Validator::make(
                ['format_values' => $formatValue],
                [
                    'format_values' => 'has_key:unit,rule,default,value',
                ]
            );
            if ($validator->fails()) {
                $msg = $validator->errors()->first();
                throw new AppException($msg, GoodsSpecConstraintsException::GOODS_CONSTRAINT_FORMAT_VALUE_KEY_MISS);
            }
        }
    }
}
