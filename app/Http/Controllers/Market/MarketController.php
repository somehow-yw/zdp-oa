<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Services\Market\MarketService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    private $service;

    public function __construct(MarketService $marketService)
    {
        $this->service = $marketService;
    }

    /**
     * 获取已开通省市列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->validate(
            $request,
            [
                'page' => 'integer|min:1|max:999',
                'size' => 'integer|min:1|max:50',
            ]
        );

        $data = $this->service->index(
            $request->input('page', 1),
            $request->input('size', 20)
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 查看省市开通市场
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $this->validate(
            $request,
            [
                'divideid' => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'page'     => 'integer|min:1|max:999',
                'size'     => 'integer|min:1|max:50',
            ], [
                'divideid.exists' => '该省还未开通市场',
            ]
        );

        $data = $this->service->show(
            $request->input('divideid'),
            $request->input('page', 1),
            $request->input('size', 20)
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 开通省市
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function openProvince(Request $request)
    {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|min:1|max:34|unique:mysql_zdp_main.dp_pianqu_divide,provinceidtxt',
            ],
            [
                'id.unique' => '该省已开通',
                'id.min'    => '传入数据不合法',
                'id.max'    => '传入数据不合法',
            ]
        );

        $this->service->openProvince($request->input('id'));

        return response()->json([
            'code'    => 0,
            'message' => '开通省份成功',
            'data'    => [],
        ]);
    }

    /**
     * 开通市场
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function openMarket(Request $request)
    {
        $this->validate(
            $request,
            [
                'pid'       => 'required|integer|min:1|max:34|exists:mysql_zdp_main.dp_pianqu_divide,provinceidtxt',
                'cid'       => 'required|integer',
                'county_id' => 'required|integer',
                'name'      => 'required|string|min:1|max:16',
            ], [
                'pid.min'    => '传入数据不合法',
                'pid.max'    => '传入数据不合法',
                'pid.exists' => '该省份还未开通，不能添加市场',
            ]
        );

        $this->service->openMarket(
            $request->input('pid'),
            $request->input('cid'),
            $request->input('county_id'),
            $request->input('name')
        );

        return response()->json([
            'code'    => 0,
            'message' => '添加成功',
            'data'    => [],
        ]);
    }
}