<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/8/16
 * Time: 14:45
 */

namespace App\Http\Controllers\External\Ious;

use App\Services\External\Ious\XimuIousService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class XimuIousController extends Controller
{
    /**
     * 徙木冻品贷白名单获取
     *
     * @param \Illuminate\Http\Request                    $request
     * @param \App\Services\External\Ious\XimuIousService $iousService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request, XimuIousService $iousService)
    {
        $this->validate(
            $request,
            [
                'page'        => 'required|integer|min:1',
                'size'        => 'required|integer|max:50',
                'mobile'      => 'string|between:11,11',
                'province_id' => 'integer|min:0',
                'open_status' => 'integer|in:0,1,5',
                'pay_status'  => 'integer|in:0,1,2',
            ],
            [
                'page.required' => '当前查询页数必须有',
                'page.integer'  => '当前查询页数应是一个整型',
                'page.min'      => '当前查询页数不可小于:min',

                'size.required' => '每页数据量必须有',
                'size.integer'  => '每页数据量必须是一个整型',
                'size.max'      => '每页数据量不可大于:max',

                'mobile.string'  => '手机号必须是一个字符串',
                'mobile.between' => '手机号长度必须是:min位',

                'province_id.integer' => '省份ID必须是一个整型',
                'province_id.min'     => '省份ID不可小于:min',

                'open_status.integer' => '开通状态必须是一个整型',
                'open_status.in'      => '开通状态不在指定范围内',

                'pay_status.integer' => '支付状态必须是一个整型',
                'pay_status.in'      => '支付状态不在指定范围内',
            ]
        );
        $page = $request->input('page');
        $size = $request->input('size');
        $queryArr = [
            'mobile'      => $request->input('mobile', null),
            'province_id' => $request->input('province_id', null),
            'open_status' => $request->input('open_status', null),
            'pay_status'  => $request->input('pay_status', null),
        ];
        $dataArr = $iousService->getList($page, $size, $queryArr);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $dataArr,
        ];

        return response()->json($reDataArr);
    }
}
