<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/28
 * Time: 20:35
 */

namespace App\Utils;

class RequestDataEncapsulationUtil
{
    /**
     * 对请求数据进行签名处理
     *
     * @param array  $requestDataArr 需进行签名的数据 键值对 如：['user_name'=>'xxx']
     * @param string $signKey        进行签名的密钥
     *
     * @return array 包含了签名的请求数据
     */
    public static function requestDataSign($requestDataArr, $signKey)
    {
        $signTimestamp = time();
        krsort($requestDataArr);
        $inputText = '';
        foreach ($requestDataArr as $key => $value) {
            if (isset($value)) {
                $inputText .= "&{$key}={$value}";
            }
        }
        $inputText = substr($inputText, 1);

        $signature = md5(hash_hmac('sha1', $signTimestamp . $inputText, $signKey, true));

        // 将签名用数据封装进请求
        $requestDataArr['timestamp'] = $signTimestamp;
        $requestDataArr['signature'] = $signature;

        return $requestDataArr;
    }

    /**
     * HTTP请求数据签名处理
     *
     * @param array  $requestDataArr 需进行签名的数据 键值对 如：['user_name'=>'xxx']
     * @param string $signKey        进行签名的密钥
     * @return array 包含了签名的请求数据
     */
    public static function getHttpRequestSign($requestDataArr, $signKey)
    {
        $signTimestamp = time();
        $requestDataArr = array_sort_recursive($requestDataArr);
        $inputText = json_encode($requestDataArr, JSON_NUMERIC_CHECK);
        $signature = md5(hash_hmac('sha1', $signTimestamp . $inputText, $signKey, true));

        // 将签名用数据封装进请求
        $requestDataArr['timestamp'] = $signTimestamp;
        $requestDataArr['signature'] = $signature;

        return $requestDataArr;
    }

    /**
     * 接收到HTTP请求数据的签名验证
     *
     * @param array  $requestDataArr 验证签名的数据 键值对 如：['user_name'=>'xxx']
     * @param string $signKey        进行签名的密钥
     * @return bool
     */
    public static function verifyHttpReceiveDataSign($requestDataArr, $signKey)
    {
        $requestDataSignTime = empty($requestDataArr['timestamp']) ? '' : $requestDataArr['timestamp'];
        $requestDataSign = empty($requestDataArr['signature']) ? '' : $requestDataArr['signature'];
        unset($requestDataArr['timestamp']);
        unset($requestDataArr['signature']);

        $requestDataArr = array_sort_recursive($requestDataArr);
        $inputText = json_encode($requestDataArr, JSON_NUMERIC_CHECK);
        $signature = md5(hash_hmac('sha1', $requestDataSignTime . $inputText, $signKey, true));

        return $signature == $requestDataSign;
    }
}
