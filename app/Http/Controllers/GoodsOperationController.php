<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/25
 * Time: 14:00
 */

namespace App\Http\Controllers;

use App\Services\Goods\GoodsOperationService;
use Illuminate\Http\Request;

/**
 * Class GoodsOperationController.
 * 商品操作处理
 *
 * @package App\Http\Controllers
 */
class GoodsOperationController extends Controller
{
    /**
     * 下架普通商品
     *
     * @param Request               $request
     * @param GoodsOperationService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function soldOutOrdinaryGoods(Request $request, GoodsOperationService $service)
    {
        $this->validate(
            $request,
            [
                'goods_id'        => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
                'sold_out_reason' => 'required|string|max:100',
                'notify_way'      => 'required|integer|in:0,1,2',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.exists'   => '商品id不存在',

                'sold_out_reason.required' => '下架原因必须有',
                'sold_out_reason.string'   => '下架原因必须是个字符串',
                'sold_out_reason.max'      => '下架原因不能超过:max个字符',

                'notify_way.required' => '通知方式必须有',
                'notify_way.integer'  => '通知方式必须是个整数',
                'notify_way.in'       => '通知方式只能是0,1,2',
            ]
        );

        $service->soldOutOrdinaryGoods(
            $request->input('goods_id'),
            $request->input('sold_out_reason'),
            $request->input('notify_way')
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
     * 刷新普通商品价格
     *
     * @param Request               $request
     * @param GoodsOperationService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function refreshOrdinaryGoodsPrice(Request $request, GoodsOperationService $service)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.exists'   => '商品id不存在',
            ]
        );
        $service->refreshOrdinaryGoodsPrice($request->input('goods_id'));


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
     * 删除普通商品
     *
     * @param Request               $request
     * @param GoodsOperationService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteOrdinaryGoods(Request $request, GoodsOperationService $service)
    {
        $this->validate(
            $request,
            [
                'goods_id'      => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
                'delete_reason' => 'required|string|max:100',
                'notify_way'    => 'required|integer|in:0,1,2',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.exists'   => '商品id不存在',

                'delete_reason.required' => '删除原因必须有',
                'delete_reason.string'   => '删除原因必须是个字符串',
                'delete_reason.max'      => '删除原因不能超过:max个字符',

                'notify_way.required' => '通知方式必须有',
                'notify_way.integer'  => '通知方式必须是个整数',
                'notify_way.in'       => '通知方式只能是0,1,2',
            ]
        );
        $service->deleteOrdinaryGoods(
            $request->input('goods_id'),
            $request->input('delete_reason'),
            $request->input('notify_way')
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
     * 上架普通商品
     *
     * @param Request               $request
     * @param GoodsOperationService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function onSaleOrdinaryGoods(Request $request, GoodsOperationService $service)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.exists'   => '商品id不存在',
            ]
        );

        $service->onSaleOrdinaryGoods($request->input('goods_id'));


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
     * 恢复删除商品
     *
     * @param Request               $request
     * @param GoodsOperationService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function unDeleteOrdinaryGoods(Request $request, GoodsOperationService $service)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是个整数',
                'goods_id.exists'   => '商品id不存在',
            ]
        );

        $service->unDeleteOrdinaryGoods($request->input('goods_id'));


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
     * 商品图片删除
     *
     * @param Request               $request
     * @param GoodsOperationService $goodsOperationService
     *
     * @return \Illuminate\Http\Response
     */
    public function delGoodsPicture(Request $request, GoodsOperationService $goodsOperationService)
    {
        $this->validate(
            $request,
            [
                'picture_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_pic,picid',
            ],
            [
                'picture_id.required' => '图片id不能为空',
                'picture_id.integer'  => '图片id必须是一个整形',
                'picture_id.min'      => '图片id不能小于:min',
                'picture_id.exists'   => '图片不存在',
            ]
        );
        $goodsOperationService->delGoodsPicture($request->input('picture_id'));
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
     * 检验报告图片删除
     *
     * @param Request               $request
     * @param GoodsOperationService $goodsOperationService
     *
     * @return \Illuminate\Http\Response
     */
    public function delGoodsInspectionReport(Request $request, GoodsOperationService $goodsOperationService)
    {
        $this->validate(
            $request,
            [
                'picture_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_inspection_reports,id',
            ],
            [
                'picture_id.required' => '图片id不能为空',
                'picture_id.integer'  => '图片id必须是一个整形',
                'picture_id.min'      => '图片id不能小于:min',
                'picture_id.exists'   => '图片不存在',
            ]
        );
        $goodsOperationService->delGoodsInspectionReport($request->input('picture_id'));
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
     * 审核通过处理
     *
     * @param Request               $request
     * @param GoodsOperationService $goodsOperationService
     *
     * @return \Illuminate\Http\Response
     */
    public function auditPass(Request $request, GoodsOperationService $goodsOperationService)
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
        $goodsOperationService->auditPass($request->input('goods_id'));
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
     * 审核拒绝处理
     *
     * @param Request               $request
     * @param GoodsOperationService $goodsOperationService
     *
     * @return \Illuminate\Http\Response
     */
    public function auditRefused(Request $request, GoodsOperationService $goodsOperationService)
    {
        $this->validate(
            $request,
            [
                'goods_id'       => 'required|integer|min:1|exists:mysql_zdp_main.dp_goods_info,id',
                'refused_reason' => 'required|string|between:5,100',
                'notice_way'     => 'required|integer|between:0,2',
            ],
            [
                'goods_id.required' => '商品id不能为空',
                'goods_id.integer'  => '商品id必须是一个整形',
                'goods_id.min'      => '商品id不能小于:min',
                'goods_id.exists'   => '商品不存在',

                'refused_reason.required' => '拒绝理由不能为空',
                'refused_reason.string'   => '拒绝理由必须是一个字符串',
                'refused_reason.between'  => '拒绝理由长度应在:min到:max个字',

                'notice_way.required' => '通知方式不能为空',
                'notice_way.integer'  => '通知方式必须是一个整形',
                'notice_way.between'  => '通知方式应该是:min到:max的整数',
            ]
        );
        $goodsOperationService->auditRefused(
            $request->input('goods_id'),
            $request->input('refused_reason'),
            $request->input('notice_way')
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
}
