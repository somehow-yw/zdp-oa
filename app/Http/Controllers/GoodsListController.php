<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/25
 * Time: 13:25
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Goods\GoodsListService;

/**
 * Class GoodsListController.
 * 商品列表
 *
 * @package App\Http\Controllers
 */
class GoodsListController extends Controller
{
    /**
     * 待审核商品列表
     *
     * @param Request          $request
     * @param GoodsListService $goodsListService
     *
     * @return \Illuminate\Http\Response
     */
    public function getNewGoodsList(Request $request, GoodsListService $goodsListService)
    {
        $this->validate(
            $request,
            [
                'size'         => 'required|integer|between:1,100',
                'page'         => 'required|integer|between:1,99999',
                'shop_id'      => 'integer|min:1|exists:mysql_zdp_main.dp_shopInfo,shopId',
                'goods_status' => 'required|integer|in:0,1,5,6',
                'sort_field'   => 'string|in:id,updated_at',
                'market_id'    => 'min:1|exists:mysql_zdp_main.dp_pianqu,pianquId',
                'area_id'      => 'required|integer|min:1|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'signing'      => 'boolean',
            ],
            [
                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'page.required'   => '当前页数必须有',
                'page.integer'    => '当前页数必须是一个整型',
                'page.between'    => '当前页数必须是:min, 到:max的整数',

                //'shop_id.required' => '店铺id必须有',
                'shop_id.integer' => '店铺id必须是个整数',
                'shop_id.min'     => '店铺id不可小于:min',
                'shop_id.exists'  => '店铺id不存在',

                'goods_status.required' => '商品状态不能为空',
                'goods_status.integer'  => '商品状态必须是个整数',
                'goods_status.in'       => '商品状态只能是:0,1,5,6',

                'sort_field.string' => '排序字段必须是一个字符串类型',
                'sort_field.in'     => '排序字段只能是:id,updated_at',

                'market_id.integer' => '市场id必须是个整数',
                'market_id.min'     => '市场id不可小于:min',
                'market_id.exists'  => '市场id不存在',

                'area_id.required' => '片区id必须有',
                'area_id.integer'  => '片区id必须是个整数',
                'area_id.min'      => '片区id不可小于:min',
                'area_id.exists'   => '片区id不存在',

                'signing.boolean' => '是否签约应该是一个布尔值',
            ]
        );

        $reData = $goodsListService->getNewGoodsList(
            $request->input('shop_id', 0),
            $request->input('goods_status'),
            $request->input('size'),
            $request->input('page'),
            $request->input('sort_field', 'id'),
            $request->input('market_id', 0),
            $request->input('area_id'),
            $request->input('signing', false)
        );
        $reDataArr = [
            'data'    => $reData,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }

    /**
     * 获取普通商品列表
     *
     * @param Request          $request
     * @param GoodsListService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getOrdinaryGoodsList(Request $request, GoodsListService $service)
    {
        $this->validate(
            $request,
            [
                'size'           => 'required|integer|between:1,5000',
                'page'           => 'required|integer|between:1,99999',
                'area_id'        => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'market_id'      => 'required|integer|min:0',
                'goods_type_id'  => 'required|integer|exists:mysql_zdp_main.dp_goods_types,id',
                'on_sale_status' => 'required|integer|in:0,1,2',
                'audit_status'   => 'required|integer|in:0,2,4',
                'unit'           => 'required|integer|min:-1',
                'price_status'   => 'required|integer|min:0',
                'key_words'      => 'string',
                'query_by'       => 'required_with:key_words|string|in:goods_name,supplier_name,brand',
                'order_by'       => 'string|in:goods_id,price,price_updated_at',
                'aesc'           => 'boolean',
            ],
            [
                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'area_id.required' => '大区id不能为空',
                'area_id.integer'  => '大区id必须是个整数',
                'area_id.exists'   => '大区id不存在',

                'market_id.required' => '市场id不能为空',
                'market_id.integer'  => '市场id必须是个整数',
                'market_id.min'      => '市场id不能小于:min',

                'goods_type_id.required' => '商品类型id必须有',
                'goods_type_id.integer'  => '商品类型id必须是个整数',
                'goods_type_id.exists'   => '商品类型id不存在',

                'on_sale_status.required' => '商品上下架状态不能为空',
                'on_sale_status.integer'  => '商品上下架状态必须是个整数',
                'on_sale_status.in'       => '商品上下架状态只能是0,1,2',

                'audit_status.required' => '商品审核状态不能为空',
                'audit_status.integer'  => '商品审核状态必须是个整数',
                'audit_status.in'       => '商品审核状态只能是0,2,4',

                'unit.required' => '单位id不能为空',
                'unit.integer'  => '单位id必须是数字',
                'unit.min'      => '单位id不能小于:min',

                'price_status.required' => '价格状态不能为空',
                'price_status.integer'  => '价格状态必须是个整数',
                'price_status.min'      => '价格状态不能小于:min',

                'key_words.string' => '关键字必须是字符串',

                'query_by.required_with' => '通过什么查询必须指定',
                'query_by.string'        => '通过什么查询必须是个字符串',
                'query_by.in'            => '只能通过goods_name,supplier_name,brand查询',

                'order_by.string' => '排序字段必须是字符串',
                'order_by.in'     => '排序字段只能是goods_id,price,price_updated_at',

                'aesc.boolean' => '是否升序必须是个布尔值',
            ]
        );
        // 市场id为0代表所有市场
        $marketId = $request->input('market_id');
        if (0 == $marketId) {
            $marketId = null;
        }
        // 商品上下架状态为0代表所有商品上下架状态
        $onSaleStatus = $request->input('on_sale_status');
        if (0 == $onSaleStatus) {
            $onSaleStatus = null;
        }
        $auditStatus = $request->input('audit_status');
        if (0 == $auditStatus) {
            $auditStatus = null;
        }
        $unit = $request->input('unit');
        // 单位为-1为所有单位
        if (-1 == $unit) {
            $unit = null;
        }
        // 价格状态为0为所有价格状态
        $priceStatus = $request->input('price_status');
        if (0 == $priceStatus) {
            $priceStatus = null;
        }
        // 查询关键字默认为空
        $keyWords = null;
        if ($request->has('key_words')) {
            $keyWords = $request->input('key_words');
        }
        // 排序字段默认为goods_id
        $orderBy = 'goods_id';
        if ($request->has('order_by')) {
            $orderBy = $request->input('order_by');
        }
        // 默认降序排列
        $aesc = false;
        if ($request->has('aesc')) {
            $aesc = boolval($request->input('aesc'));
        }

        $goodsList = $service->getGoodsList(
            $request->input('area_id'),
            $request->input('size'),
            $request->input('goods_type_id'),
            $marketId,
            $onSaleStatus,
            $auditStatus,
            $unit,
            $priceStatus,
            $keyWords,
            $request->input('query_by'),
            $orderBy,
            $aesc
        );

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'page'        => (int)$request->input('page'),
                'total'       => $goodsList->total(),
                'goods_lists' => $goodsList->toArray()['data'],
            ],
        ];

        return $this->render(
            'goods.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 获取商品历史价格列表
     *
     * @param Request          $request
     * @param GoodsListService $goodsListService
     *
     * @return \Illuminate\Http\Response
     */
    public function getHistoryPricesList(Request $request, GoodsListService $goodsListService)
    {
        $this->validate(
            $request,
            [
                'size'     => 'required|integer|between:1,100',
                'page'     => 'required|integer|between:1,99999',
                'goods_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goodprice_change,goodid',
            ],
            [
                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'goods_id.required' => '商品id必须有',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.min'      => '商品id不可小于:min',
                'goods_id.exists'   => '没有该商品id的改价记录',
            ]
        );

        $reData = $goodsListService->getHistoryPricesList(
            $request->input('goods_id'),
            $request->input('page'),
            $request->input('size')
        );
        $reDataArr = [
            'data'    => $reData,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }

    /**
     * 获取商品操作日志
     *
     * @param Request          $request
     * @param GoodsListService $goodsListService
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsOperationLogs(Request $request, GoodsListService $goodsListService)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
                'size'     => 'required|integer|between:1,100',
                'page'     => 'required|integer|between:1,99999',
            ],
            [
                'goods_id.required' => '商品id必须有',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.exists'   => '商品id不存在',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',
            ]
        );

        $reData = $goodsListService->getGoodsOperationLogsList(
            (int)$request->input('goods_id'),
            (int)$request->input('page'),
            (int)$request->input('size')
        );

        $reDataArr = [
            'data'    => $reData,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'goods.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }
}
