<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Shops\AppraiseHandleService;
use Illuminate\Http\Request;
use App\Http\Controllers\Shop\RequestTraits\AppraiseRequest;
use Illuminate\Support\Facades\Auth;

class AppraiseHandleController extends Controller
{

    use AppraiseRequest;

    protected $service;

    public function __construct(AppraiseHandleService $service)
    {
        $this->service = $service;
    }

    /*
     * 根据条件获取评价列表
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getList(Request $request)
    {
        $this->validate(
            $request,
            [
                'shop_name'         => 'string',
                'goods_name'        => 'string',
                'orderIds'          => 'string',
                'start_time'        => 'string',
                'end_time'          => 'string',
                'size'              => 'integer',
                'page'              => 'integer',
            ],
            [
                'shop_name.string'      =>'店铺名字必须是字符串',

                'goods_name.string'      =>'商品名称必须是字符串',

                'orderIds.string'       =>'订单编号必须是字符串',

                'start_time.string'     =>'开始时间必须是字符串',

                'end_time.string'       =>'结束时间必须是字符串',

                'size.integer'          =>'每页显示的条数必须为整数',

                'page.integer'          =>'页数必须为整数'

            ]
        );

        $reData = $this->service->getList(
            $request->input('shop_name', ''),
            $request->input('goods_name', ''),
            $request->input('orderIds', ''),
            $request->input('start_time', ''),
            $request->input('end_time', ''),
            $request->input('size', '20'),
            $request->input('page', '1')
        );

        return response()->json([
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ]);
    }

    /**
     * 根据订单号获取对应的评价详情
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAppraiseDetails(Request $request)
    {
        $this->validate(
            $request,
            [
                'orderIds' => 'required|string',
            ],
            [
                'orderIds.required'     => '订单号不能为空',
                'orderIds.string'       => '订单号必须是字符串',
            ]
        );

        $reData = $this->service->getAppraiseDetails($request->input('orderIds'));


        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => $reData,
        ]);
    }

    /**
     * 更新（修改）评价记录
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAppraise(Request $request)
    {
        // 验证
        $this->appraiseValidate($request);
        // 记录评价内容
        $reData = $this->service->updateOrderAppraise(
            $request->input('sub_order_no'),
            $request->input('goods_appraises'),
            $request->input('shop_appraises')
        );



        $reDataArr = [
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 根据订单号获取对应订单的评价的修改日志信息
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAppraiseLog(Request $request)
    {
        $this->validate(
            $request,
            [
                'orderIds' => 'required|string',
            ],
            [
                'orderIds.required'     => '订单号不能为空',
                'orderIds.string'       => '订单号必须是字符串',
            ]
        );

        $reData = $this->service->getAppraiseLog($request->input('orderIds'));


        return response()->json([
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ]);
    }

    /**
     * 根据子订单号软删除对应的评价
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAppraise(Request $request)
    {
        $this->validate(
            $request,
            [
                'orderIds' => 'required|string',
            ],
            [
                'orderIds.required'     => '订单号不能为空',
                'orderIds.string'       => '订单号必须是字符串',
            ]
        );

        $reData = $this->service->deleteAppraise($request->input('orderIds'));


        return response()->json([
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ]);
    }

    /**
     * 根据子订单号重置评价
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetAppraise(Request $request)
    {
        $this->validate(
            $request,
            [
                'orderIds' => 'required|string',
            ],
            [
                'orderIds.required'     => '订单号不能为空',
                'orderIds.string'       => '订单号必须是字符串',
            ]
        );

        $reData = $this->service->resetAppraise($request->input('orderIds'));


        return response()->json([
            'code'    => $reData['code'],
            'message' => $reData['message'],
            'data'    => $reData['data'],
        ]);
    }

    /**
     * 评价数据统计(店铺)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appraiseShopInfo(
        Request $request
    ){
        $startTime = $request->input('start_time');
        $this->validate(
            $request,
            [
                'start_time'       => 'required|date_format:Y-m-d',
                'end_time'         => "required|date_format:Y-m-d|after:$startTime",
                'province'         => 'string',
                'city'             => 'string',
                'district'         => 'string',
                'seek'             => 'string',
                'seekVal'          => 'string',
                'page_size'        => 'required|integer',
                'page_num'         => 'required|integer',
                'type'             => 'required|integer|between:0,1',
                'sort_type'        => 'integer|between:1,4',
                'sort_type_way'    => 'string|between:3,4'
            ]
        );
        $reData = $this->service->appraiseShopInfo(
            $request->input('start_time'),
            $request->input('end_time'),
            $request->input('province'),
            $request->input('city'),
            $request->input('district'),
            $request->input('seek'),
            $request->input('seekVal'),
            $request->input('page_size'),
            $request->input('page_num'),
            $request->input('type'),
            $request->input('sort_type'),
            $request->input('sort_type_way','desc')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ]);
    }

    /**
     * 服务商评价数据统计（商品）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appraiseGoodsInfo(
        Request $request
    ){
        $startTime = $request->input('start_time');
        $this->validate(
            $request,
            [
                'start_time'       => 'required|date_format:Y-m-d',
                'end_time'         => "required|date_format:Y-m-d|after:$startTime",
                'province'         => 'string',
                'city'             => 'string',
                'district'         => 'string',
                'seekVal'          => 'string',
                'page_size'        => 'required|integer',
                'page_num'         => 'required|integer',
                'sort_type'        => 'integer',
                'sort_type_way'    => 'string|between:3,3'
            ]
        );
        $reData = $this->service->appraiseGoodsInfo(
            $request->input('start_time'),
            $request->input('end_time'),
            $request->input('province'),
            $request->input('city'),
            $request->input('district'),
            $request->input('seekVal'),
            $request->input('page_size'),
            $request->input('page_num'),
            $request->input('sort_type'),
            $request->input('sort_type_way','desc')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ]);
    }
}