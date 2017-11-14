<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/4
 * Time: 18:12
 */

namespace App\Services;

use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;

class AreaService
{
    private $httpRequest;

    private $mainSignKey;

    private $mainRequestUrl;

    public function __construct(
        HTTPRequestUtil $httpRequest
    ) {
        $this->httpRequest = $httpRequest;
        $this->mainSignKey = config('signature.main_sign_key');
        $this->mainRequestUrl = config('request_url.main_request_url');
    }

    /**
     * 行政区域列表
     *
     * @return string
     */
    public function getAreaList()
    {
        $requestDataArr = [
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr =
            RequestDataEncapsulationUtil::requestDataSign($requestDataArr,
                $this->mainSignKey);

        $requestUrl = $this->mainRequestUrl . '/other/area/list';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->get($requestUrl, $signRequestDataArr,
            $headersArr);

        return $reData;
    }

    /**
     * 自定义大区数据获取
     *
     * @return string
     */
    public function getCustomAreaList()
    {
        $requestDataArr = [
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr =
            RequestDataEncapsulationUtil::requestDataSign($requestDataArr,
                $this->mainSignKey);

        $requestUrl = $this->mainRequestUrl . '/other/custom-area/list';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->get($requestUrl, $signRequestDataArr,
            $headersArr);

        return $reData;
    }
}
