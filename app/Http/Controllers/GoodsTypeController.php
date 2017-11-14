<?php
/**
 * Created by PhpStorm.
 * 商品分类处理.
 *
 * User: fer
 * Date: 2016/9/26
 * Time: 15:28
 */

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Services\Goods\GoodsTypeService;

/**
 * Class GoodsTypeController.
 * 商品分类请求管理
 *
 * @package App\Http\Controllers
 */
class GoodsTypeController extends Controller
{
    /**
     * 商品分类添加
     *
     * @param Request          $request
     * @param GoodsTypeService $goodsTypeService
     *
     * @return \Illuminate\Http\Response
     */
    public function addGoodsType(Request $request, GoodsTypeService $goodsTypeService)
    {
        $this->validate(
            $request,
            [
                'area_id'       => 'required|integer|min:2|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'parent_id'     => 'required|integer|min:0',
                'type_name'     => 'required|string|between:1,6',
                'type_keywords' => 'required|string|between:1,255',
                'type_pic_url'  => 'string|between:5,255',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',
                'area_id.exists'   => '大区ID不存在',

                'parent_id.required' => '父ID必须有',
                'parent_id.integer'  => '父ID应该是一个整型',
                'parent_id.min'      => '父ID不可小于:min',

                'type_name.required' => '类型名称必须有',
                'type_name.string'   => '类型名称应该是一个字符串',
                'type_name.between'  => '类型名称长度应在:min到:max',

                'type_keywords.required' => '分类关键词必须有',
                'type_keywords.string'   => '分类关键词应该是一个逗号分隔的字符串',
                'type_keywords.between'  => '分类关键词长度应在:min到:max',

                //'type_pic_url.required' => '分类图标必须有',
                'type_pic_url.string'   => '分类图标URL必须是字符串',
                'type_pic_url.between'  => '分类图标URL长度应在:min到:max',
            ]
        );

        $reData = $goodsTypeService->addGoodsType(
            $request->input('area_id'),
            $request->input('parent_id', 0),
            $request->input('type_name'),
            $request->input('type_keywords'),
            $request->input('type_pic_url')
        );

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品分类列表
     *
     * @param Request          $request
     * @param GoodsTypeService $goodsTypeService
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsTypeList(Request $request, GoodsTypeService $goodsTypeService)
    {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:2|exists:mysql_zdp_main.dp_pianqu_divide,id',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',
                'area_id.exists'   => '大区ID不存在',
            ]
        );

        $reData = $goodsTypeService->getGoodsTypeList($request->input('area_id'));

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 获取当前商品分类详情
     *
     * @param Request          $request
     * @param GoodsTypeService $goodsTypeService
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsTypeInfo(Request $request, GoodsTypeService $goodsTypeService)
    {
        $this->validate(
            $request,
            [
                'type_id' => 'required|integer|min:1',
            ],
            [
                'type_id.required' => '分类ID必须有',
                'type_id.integer'  => '分类ID应该是一个整型',
                'type_id.min'      => '分类ID不可小于:min',
            ]
        );

        $reData = $goodsTypeService->getGoodsTypeInfo($request->input('type_id'));

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品分类修改
     *
     * @param Request          $request
     * @param GoodsTypeService $goodsTypeService
     *
     * @return \Illuminate\Http\Response
     */
    public function updateGoodsType(Request $request, GoodsTypeService $goodsTypeService)
    {
        $this->validate(
            $request,
            [
                'type_id'       => 'required|integer|min:1',
                'type_name'     => 'required|string|between:1,6',
                'type_keywords' => 'required|string|between:1,255',
                'type_pic_url'  => 'string|between:5,255',
            ],
            [
                'type_id.required' => '分类ID必须有',
                'type_id.integer'  => '分类ID应该是一个整型',
                'type_id.min'      => '分类ID不可小于:min',

                'type_name.required' => '类型名称必须有',
                'type_name.string'   => '类型名称应该是一个字符串',
                'type_name.between'  => '类型名称长度应在:min到:max',

                'type_keywords.required' => '分类关键词必须有',
                'type_keywords.string'   => '分类关键词应该是一个逗号分隔的字符串',
                'type_keywords.between'  => '分类关键词长度应在:min到:max',

                //'type_pic_url.required' => '分类图标必须有',
                'type_pic_url.string'   => '分类图标URL必须是字符串类型',
                'type_pic_url.between'  => '分类图标URL长度应在:min到:max',
            ]
        );

        $reData = $goodsTypeService->updateGoodsType(
            $request->input('type_id'),
            $request->input('type_name'),
            $request->input('type_keywords'),
            $request->input('type_pic_url')
        );

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品分类删除
     *
     * @param Request          $request
     * @param GoodsTypeService $goodsTypeService
     *
     * @return \Illuminate\Http\Response
     */
    public function delGoodsType(Request $request, GoodsTypeService $goodsTypeService)
    {
        $this->validate(
            $request,
            [
                'type_id' => 'required|integer|min:1|unique:mysql_zdp_main.dp_goods_info,goods_type_id',
            ],
            [
                'type_id.required' => '分类ID必须有',
                'type_id.integer'  => '分类ID应该是一个整型',
                'type_id.min'      => '分类ID不可小于:min',
                'type_id.unique'   => '存在商品的分类不可被删除',
            ]
        );

        $reData = $goodsTypeService->delGoodsType($request->input('type_id'));

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品分类排序
     *
     * @param Request          $request
     * @param GoodsTypeService $goodsTypeService
     *
     * @return \Illuminate\Http\Response
     */
    public function sortGoodsType(Request $request, GoodsTypeService $goodsTypeService)
    {
        $typeSortArr = $request->all();
        if (!is_array($typeSortArr) || count($typeSortArr) < 1) {
            return $this->renderError('参数必须是一个不为空的JSON数组');
        }
        unset($typeSortArr['r']);
        foreach ($typeSortArr as $key => $typeSort) {
            $index = $key + 1;
            $validatorErr = Validator::make(
                $typeSort,
                [
                    'type_id'    => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_types,id',
                    'sort_value' => 'required|integer|min:1',
                ],
                [
                    'type_id.required' => "修改的第{$index}个分类id不能为空",
                    'type_id.integer'  => "修改的第{$index}个分类id必须是个整数",
                    'type_id.min'      => "修改的第{$index}个分类id不可小于:min",
                    'type_id.exists'   => "修改的第{$index}个分类不存在",

                    'sort_value.required' => "修改的第{$index}个分类排序值不能为空",
                    'sort_value.integer'  => "修改的第{$index}个分类排序值必须是个整数",
                    'sort_value.min'      => "修改的第{$index}个分类排序值不可小于:min",
                ]
            );
            if ($validatorErr->fails()) {
                $message = $validatorErr->errors()->first();

                return $this->renderError($message);
            }
        }
        $goodsTypeService->sortGoodsType($typeSortArr);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $this->render(
            'goods.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }
}
