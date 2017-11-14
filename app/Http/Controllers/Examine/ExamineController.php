<?php

namespace App\Http\Controllers\Examine;

use App\Services\Examine\ExamineService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ExamineController extends Controller
{
    private $service;

    public function __construct(ExamineService $examineService)
    {
        $this->service = $examineService;
    }

    /**
     * 审核通过
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pass(Request $request)
    {
        $this->validate(
            $request,
            [
                'shId'     => 'required|integer', // 用户Id
            ]
        );

        $this->service->pass($request->input('shId'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 拒绝
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refuse(Request $request)
    {
        $this->validate(
            $request,
            [
                'shopId'        => 'required|integer|exists:mysql_zdp_main.dp_shopInfo,shopId',
                'refuseReason' => 'required|string|min:1|max:200',
            ]
        );

        $this->service->refuse(
            $request->input('shopId'),
            $request->input('refuseReason')
        );

        return response()->json([
            'code'    => 0,
            'message' => '操作成功',
            'data'    => [],
        ]);
    }
}
