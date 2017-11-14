<?php

namespace App\Http\Controllers\Tickling;

use App\Jobs\SendReplayTicking;
use App\Workflows\TicklWorkflow;
use EasyWeChat\Notice\Notice;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\TicklService;

class TicklController extends Controller
{
    /**
     * 获取找冻品网的反馈
     * @param Request $request
     * @param TicklService $ticklService
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetTicking(
        Request $request,
        TicklService $ticklService
    )
    {
        $this->validate(
            $request,
            [
                'type' => 'integer|between:0,2',
                'page_size' => 'integer|between:1,50',
                'page_num' => 'required|integer|min:1'
            ]
        );
        $reData = $ticklService->GetTicking(
            $request->input('type', 0),
            $request->input('page_size', 20),
            $request->input('page_num')
        );
        return response()->json([
            'code' => 0,
            'message' => 'OK',
            'data' => $reData,
        ]);
    }

    /**
     * 获取某一个反馈的详细信息
     * @param Request $request
     * @param TicklService $ticklService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTickingInfo(
        Request $request,
        TicklService $ticklService
    )
    {
        $this->validate(
            $request,
            [
                'type' => 'integer|between:0,2',
                'id' => 'required|integer|exists:' . config('main_data.connection', 'test_main') . '.dp_messages,id'
            ]
        );
        $reData = $ticklService->getTickingInfo(
            $request->input('type'),
            $request->input('id')
        );

        return response()->json([
            'code' => 0,
            'message' => 'OK',
            'data' => $reData,
        ]);
    }

    /**
     * 获取服务商的反馈
     * @param Request $request
     * @param TicklService $ticklService
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetSpTicking(
        Request $request,
        TicklService $ticklService
    )
    {
        $this->validate(
            $request,
            [
                'type' => 'integer|between:0,2',
                'page_size' => 'integer|between:1,50',
                'page_num' => 'required|integer|min:1'
            ]
        );
        $reData = $ticklService->GetSpTicking(
            $request->input('type', 0),
            $request->input('page_size', 20),
            $request->input('page_num')
        );
        return response()->json([
            'code' => 0,
            'message' => 'OK',
            'data' => $reData,
        ]);
    }

    /**获取服务商某反馈详情
     * @param Request $request
     * @param TicklService $ticklService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpTickingInfo(
        Request $request,
        TicklService $ticklService
    )
    {
        $this->validate(
            $request,
            [
                'type' => 'integer|between:0,2',
                'id' => 'required|integer|exists:' . config('main_data.connection', 'test_main') . '.dp_messages,id'
            ]
        );
        $reData = $ticklService->getSpTickingInfo(
            $request->input('type'),
            $request->input('id')
        );
        return response()->json([
            'code' => 0,
            'message' => 'OK',
            'data' => $reData,
        ]);
    }

    /**
     * 反馈回复
     * @param Request $request
     * @param TicklWorkflow $ticklWorkflow
     * @return \Illuminate\Http\JsonResponse
     */
    public function rePlay(
        Request $request,
        TicklWorkflow $ticklWorkflow
    )
    {
        $this->validate(
            $request,
            [
                'ticking_id' => 'required|integer|min:1',
                'content' => 'required|string',
                'type' => 'required|integer|between:1,2',
            ]
        );

        $ticklWorkflow->rePlay(
            $request->input('ticking_id'),
            $request->input('content'),
            $request->input('type')
        );

        return response()->json([
            'code' => 0,
            'message' => 'OK',
            'data' => null,
        ]);
    }
}
