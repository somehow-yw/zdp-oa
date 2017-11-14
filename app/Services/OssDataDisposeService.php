<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 16:22
 */

namespace App\Services;

use App\Utils\OssDataDisposeUtil;

class OssDataDisposeService
{
    /**
     * OSS请求数据签名
     *
     * @param string $signatureData 需做签名的数据
     *
     * @return array
     */
    public function getSignature($signatureData)
    {
        $signature = OssDataDisposeUtil::dataSignature($signatureData);

        $reData = [
            'signature' => $signature,
        ];

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 获得OSS请求身份数据
     *
     * @return array
     */
    public function getOssIdentityData()
    {
        $ossIdentityArr = config('oss.oss_cons_info');

        $reData = [
            'access_id' => $ossIdentityArr['access_id'],
        ];

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }
}