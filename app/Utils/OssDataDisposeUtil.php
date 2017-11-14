<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 16:30
 */

namespace App\Utils;


class OssDataDisposeUtil
{
    /**
     * OSS请求数据签名
     *
     * @param string $signatureData 需做签名的数据
     *
     * @return string
     */
    public static function dataSignature($signatureData)
    {
        $signature = base64_encode(hash_hmac('sha1', $signatureData, config('signature.oss_sign_key'), true));

        return $signature;
    }
}