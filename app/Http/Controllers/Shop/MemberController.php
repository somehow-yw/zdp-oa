<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Shops\MemberService;
use Illuminate\Http\Request;
use Zdp\Main\Data\Models\DpShangHuInfo;

class MemberController extends Controller
{
    protected $service;

    public function __construct(MemberService $service)
    {
        $this->service = $service;
    }

    /**
     * 店铺成员列表
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
                'shopId' => 'required|exists:mysql_zdp_main.dp_shopInfo,shopId',
            ]
        );

        $data = DpShangHuInfo
            ::select(['shId', 'shopId', 'laoBanHao', 'OpenID', 'xingming', 'lianxiTel'])
            ->where('shopId', $request->input('shopId'))
            ->where('shengheAct', DpShangHuInfo::STATUS_PASS)
            ->get();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 删除店铺成员
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request)
    {
        $this->validate(
            $request,
            [
                'shopId' => 'required|exists:mysql_zdp_main.dp_shopInfo,shopId',
                'shId'   => 'required|exists:mysql_zdp_main.dp_shangHuInfo,shId',
            ]
        );

        $this->service->del(
            $request->input('shopId'),
            $request->input('shId')
        );

        return response()->json([
            'code'    => 0,
            'message' => '删除成功',
            'data'    => [],
        ]);
    }

    /**
     * 店铺成员绑定(添加)
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMember(Request $request)
    {
        $this->validate(
            $request,
            [
                'shop_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_shopInfo,shopId,state,0',
                'mobile'  => 'required|string|between:11,11|mobile',
                'role'    => 'required|integer|min:1|in:1,2,3,4',
            ],
            [
                'shop_id.required' => '店铺ID必须有',
                'shop_id.integer'  => '店铺ID必须是整型',
                'shop_id.min'      => '店铺ID不可小于:min',
                'shop_id.exists'   => '店铺不存在',

                'mobile.required' => '手机号必须有',
                'mobile.string'   => '手机号必须是字符串',
                'mobile.between'  => '手机号位数不正确',
                'mobile.mobile'   => '手机号不正确',

                'role.required' => '角色编号必须有',
                'role.integer'  => '角色编号必须是整型',
                'role.min'      => '角色编号不可小于:min',
                'role.in'       => '角色编号不存在',
            ]
        );

        // 去绑定店铺成员
        $this->service->addMember(
            $request->input('shop_id'),
            $request->input('mobile'),
            $request->input('role')
        );

        $reInfo = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reInfo);
    }

    /**
     * 获取店铺可用的角色
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberRole(Request $request)
    {
        $this->validate(
            $request,
            [
                'is_boos' => 'integer|min:0|in:0,1',
            ],
            [
                'is_boos.integer' => '是否保留BOOS角色必须是整型',
                'is_boos.min'     => '是否保留BOOS角色不可小于:min',
                'is_boos.in'      => '是否保留BOOS角色值不正确',
            ]
        );

        // 获取信息
        $roleInfo = $this->service->getMemberRole($request->input('is_boos', 0));

        $reInfo = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $roleInfo,
        ];

        return response()->json($reInfo);
    }

    /**
     * 生成店铺成员添加的二维码
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberAddCode(Request $request)
    {
        $this->validate(
            $request,
            [
                'shop_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_shopInfo,shopId,state,0',
                'role'    => 'required|integer|min:1|in:1,2,3,4',
            ],
            [
                'shop_id.required' => '店铺ID必须有',
                'shop_id.integer'  => '店铺ID必须是整型',
                'shop_id.min'      => '店铺ID不可小于:min',
                'shop_id.exists'   => '店铺不存在',

                'role.required' => '角色编号必须有',
                'role.integer'  => '角色编号必须是整型',
                'role.min'      => '角色编号不可小于:min',
                'role.in'       => '角色编号不存在',
            ]
        );

        // 获取信息
        $codeInfo = $this->service->getMemberAddCode(
            $request->input('shop_id'),
            $request->input('role')
        );

        $reInfo = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $codeInfo,
        ];

        return response()->json($reInfo);
    }
}
