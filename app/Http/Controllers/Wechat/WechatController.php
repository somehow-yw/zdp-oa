<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Services\SendWeChatMessageService;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    private $service;

    public function __construct(SendWeChatMessageService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取微信分组信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = $this->service->getWechatGroup();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 更新店铺分组信息
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'openid_list' => 'required|array',
                'to_groupid'  => 'required|integer',
            ]
        );

        $this->service->setWechatGroup([
            'openid_list' => $request->input('openid_list'),
            'to_groupid'  => $request->input('to_groupid'),
        ]);

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    public function get(Request $request)
    {
        $this->validate(
            $request,
            [
                'openid'=> 'required|string|max:50'
            ]
        );

        $data = $this->service->getWechatGroupInfo(['openid'=>$request->input('openid')]);

        return response()->json($data);
    }
}