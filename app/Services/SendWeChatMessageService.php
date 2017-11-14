<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 20:25
 */

namespace App\Services;

use App;

use App\Utils\RequestDataEncapsulationUtil;
use App\Utils\HTTPRequestUtil;
use App\Repositories\Goods\Contracts\GoodsRepository;
use App\Models\DpGoodsInfo;

class SendWeChatMessageService
{
    private $httpRequest;

    public function __construct(HTTPRequestUtil $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * 每日推文消息发送
     *
     * @param array  $messageSendAreaIdArr 需发送的文章大区ID
     * @param string $message              消息内容
     *
     * @return string
     */
    public function sendMessage($messageSendAreaIdArr, $message = '')
    {
        $signKey = config('signature.wechat_sign_key');
        $requestUrl = config('request_url.wechat_request_url');
        $requestDataArr = [
            'area_ids' => json_encode($messageSendAreaIdArr),
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $signKey);

        $requestUrl = $requestUrl . '/?m=SendInform&c=WeChatInform&a=sendDailyArticle';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr, $headersArr);
    }

    /**
     * 商品[审核拒绝通过，下架，删除]时给卖家的通知消息发送
     *
     * @param $goodsId       int 商品ID
     * @param $refusedReason string 拒绝原因
     *
     * @return integer
     */
    public function sendAuditRefusedNotice($goodsId, $refusedReason)
    {
        // 获得消息接收者电话号码
        /** @var $goodsRepo GoodsRepository */
        $goodsRepo = App::make(GoodsRepository::class);
        $columnSelectArr = [
            'goods' => [
                'gname as goods_name',
                'shenghe_act as goods_status',
                'on_sale',
            ],
            'shop'  => ['dianPuName as shop_name'],
            'user'  => ['OpenID as user_wechat_openid'],
        ];
        $goodsInfoObj = $goodsRepo->getGoodsBelongShopInfoByGoodsId($goodsId,
            $columnSelectArr);
        $smsType = '商品被下架';
        if ($goodsInfoObj->goods_status == DpGoodsInfo::STATUS_REJECT) {
            $smsType = '审核被拒绝';
        } elseif ($goodsInfoObj->goods_status == DpGoodsInfo::STATUS_DEL) {
            $smsType = '商品被删除';
        }
        $signKey = config('signature.wechat_sign_key');
        $requestUrl = config('request_url.wechat_request_url');
        $requestDataArr = [
            'goods_name'         => $goodsInfoObj->goods_name,
            'refused_reason'     => $refusedReason,
            'user_wechat_openid' => $goodsInfoObj->shop->user[0]->user_wechat_openid,
            'sms_type'           => $smsType,
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $signKey);

        $requestUrl = $requestUrl . '/?m=SendInform&c=WeChatInform&a=sendAuditRefusedNotice';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr, $headersArr);

        file_put_contents('/tmp/wechatSendAuditRefusedNotice.log', $reData, FILE_APPEND);

        $smsReDataArr = json_decode($reData, true);
        if (json_last_error() === 0 && 0 == $smsReDataArr['code']) {
            // 商品处理微信通知发送成功
            return 0;
        } else {
            // 商品处理微信通知发送失败
            return 1;
        }
    }

    /**
     * 店铺[审核通过，拒绝]的通知消息发送
     *
     * @param $data       string 模板消息
     *
     * @return integer
     */
    public function sendShopPassNotice($data)
    {
        // 链接地址
        $requestUrl = config('request_url.wechat_request_url');

        $requestUrl = $requestUrl . '/index.php?m=Admin&c=Interface&a=weRegPassMessage';

        $requestDataArr = ['data'=> $data];

        $headersArr = ['Accept' => 'application/json'];

        $reData = $this->httpRequest->post($requestUrl, $requestDataArr, $headersArr);

        file_put_contents('/tmp/wechatSendPassNotice.log', $reData, FILE_APPEND);

        $smsReDataArr = json_decode($reData, true);

        if (json_last_error() === 0 && 0 == $smsReDataArr['errcode']) {
            return 0; // 发送成功
        } else {
            return 1; // 发送失败
        }
    }

    /**
     * 通过老后台获取微信分组
     */
    public function getWechatGroup()
    {
        // 链接地址
        $requestUrl = config('request_url.wechat_request_url');
        $route = '/?m=Admin&c=Interface&a=wxGroupGet';
        $requestUrl = $requestUrl . $route;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        //参数为1表示传输数据，为0表示直接输出显示。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //参数为0表示不带头文件，为1表示带头文件
        curl_setopt($ch, CURLOPT_HEADER,0);
        $output = curl_exec($ch);
        curl_close($ch);

        $weChatGroupArr = json_decode($output, true);

        return $weChatGroupArr;
    }

    /**
     * 设置微信分组
     *
     * @param $data
     *
     * @return int
     */
    public function setWechatGroup($data)
    {
       $handleData = 'data=' . json_encode($data);
        // 链接地址
        $requestUrl = config('request_url.wechat_request_url');
        $requestUrl = $requestUrl . '/?m=Admin&c=Interface&a=wxUserBatchGroupSet';

        $curl = curl_init();
        //设置提交的url
        curl_setopt($curl, CURLOPT_URL, $requestUrl);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        // 进行post提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, $handleData);
        //执行命令
        $reData = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);

        $smsReDataArr = json_decode($reData, true);
        if (empty($smsReDataArr))
        {
            array_map(function ($openid){
                $getGroupInfo = $this->getWechatGroupInfo(['openid' => $openid]);
                if ($getGroupInfo['code'] != 0){
                    throw new App\Exceptions\AppException('此用户微信分组出现错误，详情：' . $getGroupInfo['message'], $getGroupInfo['code']);
                }
            }, $data['openid_list']);
        }
        if (json_last_error() === 0 && 0 == array_get($smsReDataArr,'errcode',0)) {
            return 1;// 设置成功
        } else {
            return 0;// 设置失败
        }
    }

    /**
     * 获取微信用户分组信息
     *
     * @param $data
     *
     * @return mixed
     */
    public function getWechatGroupInfo($data)
    {
        $handleData = 'data=' . json_encode($data);
        // 链接地址
        $requestUrl = config('request_url.wechat_request_url');
        $requestUrl = $requestUrl . '/?m=Admin&c=Interface&a=getUserWechatGroupInfo';

        $curl = curl_init();
        //设置提交的url
        curl_setopt($curl, CURLOPT_URL, $requestUrl);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        // 进行post提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, $handleData);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);

        $smsReDataArr = json_decode($data, true);

        if (array_has($smsReDataArr, 'errcode'))
        {
            $reArr['code'] = array_get($smsReDataArr, 'errcode');
            $reArr['message'] = array_get($smsReDataArr, 'errmsg');
            $reArr['data'] = [];
        }else{
            $reArr['code'] = 0;
            $reArr['message'] = 'OK';
            $reArr['data'] = $smsReDataArr;
        }

        return $reArr;
    }
}
