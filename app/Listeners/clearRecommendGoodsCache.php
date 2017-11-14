<?php

namespace App\Listeners;

use App\Events\RecommendGoodsUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;

class clearRecommendGoodsCache implements ShouldQueue
{
    private $httpRequest;

    /**
     * Create the event listener.
     * clearRecommendGoodsCache constructor.
     *
     * @param HTTPRequestUtil $httpRequest
     */
    public function __construct(HTTPRequestUtil $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * Handle the event.
     *
     * @param  RecommendGoodsUpdate $event
     *
     * @return void
     */
    public function handle(RecommendGoodsUpdate $event)
    {
        $requestDataArr = [
            'area_id' => $event->areaId,
            'id'      => $event->recommendGoodsId,
            'remark'  => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign(
            $requestDataArr,
            config('signature.main_sign_key')
        );

        $requestUrl = config('request_url.main_request_url') . '/cache/recommend-goods/clear';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr, $headersArr);
    }
}
