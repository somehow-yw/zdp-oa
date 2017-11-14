<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/4
 * Time: 11:22
 */

namespace App\Services;

use DB;

use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;

class TradeService
{
    private $httpRequest;

    private $signKey;

    private $requestUrl;

    private $mainSignKey;

    private $mainRequestUrl;

    public function __construct(
        HTTPRequestUtil $httpRequest
    ) {
        $this->httpRequest = $httpRequest;
        $this->signKey = config('signature.trade_sign_key');
        $this->requestUrl = config('request_url.trade_request_url');
        $this->mainSignKey = config('signature.main_sign_key');
        $this->mainRequestUrl = config('request_url.main_request_url');
    }

    /**
     * 商贸公司列表
     *
     * @param int $page 当前页数
     * @param int $size 请求的数据量
     *
     * @return array|string
     */
    public function getTradeList($page, $size)
    {
        $requestDataArr = [
            'page'   => $page,
            'size'   => $size,
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $this->signKey);

        $requestUrl = $this->requestUrl . '/trade/list';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->get($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }

    /**
     * 商贸公司添加
     *
     * @param array $requestDataArr 商贸公司添加待处理数据 数组
     *                              --trade_infos    公司账户信息
     *                              ----shop_id 店铺ID
     *                              ----login_name 登录账号 必须是手机号
     *                              ----login_password 登录密码 6-16位
     *                              --transfer_rules 规则信息 数组
     *                              ----province_id 省份ID
     *                              ----city_id 市ID
     *                              ----county_id 区县ID
     *                              ----shop_types 店铺类型串 如：'11,12,21'
     *                              ----free_freight_order_time 前N单免运费
     *                              ----free_freight_max_amount 免运费最高金额 单位(分)
     *                              ----payment_after_arrival_time 前N单可货到付款
     *                              ----abort_time 截单时间 格式：07:00:00
     *                              ----delivery_date_rule 送达日期规则 T+n n是一个整型
     *                              ----delivery_time 送达时间 如：'下午2-6点'
     *                              ----fees_rules 配送费用规则 数组
     *                              ------from_min_amount 单件最低价格 单位(分)
     *                              ------to_max_amount 单件最高价格 单位(分)
     *                              ------freight_amount 单件运费 单位(分)
     *
     * @return array|string
     */
    public function addTrade($requestDataArr)
    {
        // 请求头中期望返回的数据格式
        $headersArr = [
            'Accept' => 'application/json',
        ];
        // 根据店铺ID取得店铺名称
        $getShopNameRequestDataArr = [
            'shop_id' => $requestDataArr['trade_infos']['shop_id'],
            'remark'  => '找冻品OA系统请求',
        ];
        $mainSignRequestDataArr = RequestDataEncapsulationUtil::requestDataSign(
            $getShopNameRequestDataArr,
            $this->mainSignKey
        );
        $getShopRequestUrl = $this->mainRequestUrl . '/shop/name';
        DB::disconnect('connections');
        $shopInfoJson = $this->httpRequest->get($getShopRequestUrl, $mainSignRequestDataArr, $headersArr);
        $shopInfoArr = json_decode($shopInfoJson, true);
        if (json_last_error() != 0) {
            return json_encode(
                [
                    'code'    => json_last_error(),
                    'message' => '店铺数据获取错误：' . json_last_error_msg(),
                    'data'    => [],
                ]
            );
        }
        if ($shopInfoArr['code']) {
            return $shopInfoJson;
        }

        // 商贸公司数据添加
        $requestDataArr['trade_infos']['company_name'] = $shopInfoArr['data']['shop_name'];
        $tradeRequestDataArr = [
            'data'   => json_encode($requestDataArr),
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($tradeRequestDataArr, $this->signKey);

        $requestUrl = $this->requestUrl . '/trade/add';
        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }

    /**
     * 商贸公司详细信息获取
     *
     * @param int $tradeId 公司ID
     *
     * @return string
     */
    public function getTradeInfo($tradeId)
    {
        // 请求头中期望返回的数据格式
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $tradeRequestDataArr = [
            'trade_id' => $tradeId,
            'remark'   => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($tradeRequestDataArr, $this->signKey);

        $requestUrl = $this->requestUrl . '/trade/info';
        $reData = $this->httpRequest->get($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }

    /**
     * 商贸公司信息修改
     *
     * @param array $requestDataJson 商贸公司添加待处理数据 JSON数据
     *                               --trade_infos    公司账户信息
     *                               ----trade_id 公司ID
     *                               ----login_name 登录账号 必须是手机号
     *                               ----login_password 登录密码 6-16位 可为空 空表示不修改
     *                               --transfer_rules 规则信息 数组
     *                               ----province_id 省份ID
     *                               ----city_id 市ID
     *                               ----county_id 区县ID
     *                               ----shop_types 店铺类型串 如：'11,12,21'
     *                               ----free_freight_order_time 前N单免运费
     *                               ----free_freight_max_amount 免运费最高金额 单位(分)
     *                               ----payment_after_arrival_time 前N单可货到付款
     *                               ----abort_time 截单时间 格式：07:00:00
     *                               ----delivery_date_rule 送达日期规则 T+n n是一个整型
     *                               ----delivery_time 送达时间 如：'下午2-6点'
     *                               ----fees_rules 配送费用规则 数组
     *                               ------from_min_amount 单件最低价格 单位(分)
     *                               ------to_max_amount 单件最高价格 单位(分)
     *                               ------freight_amount 单件运费 单位(分)
     *
     * @return array|string
     */
    public function updateTradeInfo($requestDataJson)
    {
        // 请求头中期望返回的数据格式
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $tradeRequestDataArr = [
            'data'   => $requestDataJson,
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($tradeRequestDataArr, $this->signKey);

        $requestUrl = $this->requestUrl . '/trade/info/update';
        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }

    /**
     * @param int $tradeId 公司ID
     * @param int $status  修改状态
     *
     * @return string
     */
    public function updateTradeStatus($tradeId, $status)
    {
        // 请求头中期望返回的数据格式
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $tradeRequestDataArr = [
            'trade_id' => $tradeId,
            'status'   => $status,
            'remark'   => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($tradeRequestDataArr, $this->signKey);

        $requestUrl = $this->requestUrl . '/trade/status/update';
        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }
}