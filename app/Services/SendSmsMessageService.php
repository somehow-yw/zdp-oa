<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 20:24
 */

namespace App\Services;

use App;
use App\Utils\RequestDataEncapsulationUtil;
use App\Utils\HTTPRequestUtil;

use App\Repositories\UserRepository;
use App\Repositories\Goods\Contracts\GoodsRepository;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;

class SendSmsMessageService
{
    private $httpRequest;
    private $userRepository;

    public function __construct(
        HTTPRequestUtil $httpRequest,
        UserRepository $userRepository
    ) {
        $this->httpRequest = $httpRequest;
        $this->userRepository = $userRepository;
    }

    /**
     * 消息发送
     *
     * @param array $receiverArr 消息接收者
     * @param array $message     消息内容 格式：['填充一','填充二','...']
     *
     * @return string
     * @throws AppException
     */
    public function sendMessage($receiverArr, $message = [])
    {
        // 获得消息接收者电话号码
        $userTels = $this->userRepository->getUserTelByIds($receiverArr);
        $signKey = config('signature.wechat_sign_key');
        $requestUrl = config('request_url.wechat_request_url');
        $requestDataArr = [
            'receiver' => $userTels,
            'messages' => json_encode($message),
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $signKey);

        $requestUrl = $requestUrl . '/?m=SendInform&c=SmsInform&a=dailyArticleEditRemind';

        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr);
    }

    /**
     * 商品审核拒绝通过时给卖家的通知消息发送
     *
     * @param $goodsId       int 商品ID
     * @param $refusedReason string 拒绝原因
     *
     * @return string
     */
    public function sendAuditRefusedNotice($goodsId, $refusedReason)
    {
        // 获得消息接收者电话号码
        /** @var $goodsRepo GoodsRepository */
        $goodsRepo = App::make(GoodsRepository::class);
        $columnSelectArr = [
            'goods' => ['gname as goods_name'],
            'shop'  => ['jieDanTel'],
            'user'  => ['lianxiTel as user_tel'],
        ];
        $goodsInfoObj = $goodsRepo->getGoodsBelongShopInfoByGoodsId($goodsId, $columnSelectArr);
        $signKey = config('signature.wechat_sign_key');
        $requestUrl = config('request_url.wechat_request_url');
        $userTels = $goodsInfoObj->shop->user[0]->user_tel;
        $messageArr = [$goodsInfoObj->goods_name, $refusedReason];
        $requestDataArr = [
            'receiver_tels' => $userTels,
            'messages'      => json_encode($messageArr),
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $signKey);

        $requestUrl = $requestUrl . '/?m=SendInform&c=SmsInform&a=sendAuditRefusedNotice';

        $reData = $this->httpRequest->post($requestUrl, $signRequestDataArr);

        file_put_contents('/tmp/smsSendAuditRefusedNotice.log', $reData, FILE_APPEND);

        $smsReDataArr = json_decode($reData, true);
        if (json_last_error() === 0 && 0 == $smsReDataArr['code']) {
            echo '商品处理短信通知发送成功！' . PHP_EOL;
        } else {
            echo '商品处理短信通知发送失败' . PHP_EOL;
        }
    }
}
