<?php

namespace App\Services\Examine;

use App\Exceptions\AppException;
use App\Services\SendWeChatMessageService;
use Zdp\Main\Data\Models\DpIntegralChange;
use Zdp\Main\Data\Models\DpShopInfo;
use App\Models\DpShopRefuse;
use Zdp\Main\Data\Models\DpPianquDivide;
use Zdp\Main\Data\Models\DpShangHuInfo;
use Illuminate\Contracts\Auth\Guard;
use App\Models\User;
use Zdp\Main\Data\Models\DpUnregisteredUserInfos;
use Zdp\Main\Data\Models\DpUnregisterOpenidBinding;

class ExamineService
{
    /**
     * 通过审核的消息发送
     *
     * @param $shId
     *
     * @throws AppException
     * @internal param $shopInfo
     */
    public function pass($shId)
    {
        /** @var $auth Guard */
        $auth = \App::make(Guard::class);
        /** @var User $user */
        $user = $auth->user();
        $adminId = $user->id;
        $adminName = $user->user_name;
        $userInfo = $this->getInfo($shId);

        if (empty($userInfo)) {
            throw new AppException('获取用户信息失败');
        }
        // 处理积分和店铺状态
        $this->handleShopInfo($shId, $userInfo->shengheAct, $userInfo->shopId, $userInfo->OpenID, $adminName, $adminId);
        $openid = $userInfo->OpenID;
        $url = env('OPERATE_AUDIT_NOTICE');
        $title = '您好，您的资料已经通过审核';//消息标题
        $msgName =
            $userInfo->xingming . "店铺名：" . $userInfo->dianPuName;//消息类型
        $msgPhone = $userInfo->lianxiTel; //消息名
        $msgTime = date('y-m-d h:i', time());//消息详情
        $remark = '感谢您的使用';//消息脚注
        $data = [
            "OpenID"   => $openid,
            'hostUrl'  => $url,
            'first'    => $title,
            'keyword1' => $msgName,
            'keyword2' => $msgPhone,
            'keyword3' => $msgTime,
            'remark'   => $remark,
        ];
        $data = base64_encode(json_encode($data));
        $sendWxMsg = \App::make(\App\Services\SendWeChatMessageService::class);
        $sendWxMsg->sendShopPassNotice($data);//调用微信推送模板
    }

    // 获取商户信息
    protected function getInfo($shId)
    {
        return DpShopInfo::join(
            'dp_shangHuInfo as sh',
            'sh.shopId', '=', 'dp_shopInfo.shopId'
        )
                         ->where('sh.shId', $shId)
                         ->select([
                             'sh.OpenID',
                             'sh.xingming',
                             'sh.shopId',
                             'sh.lianxiTel',
                             'sh.shengheAct',
                             'dp_shopInfo.dianPuName',
                         ])
                         ->first();
    }

    // 店铺通过审核的状态和积分处理
    protected function handleShopInfo($shId, $shengheAct, $shopId, $openId, $adminName, $adminId)
    {
        \DB::connection('mysql_zdp_main')->transaction(function () use (
            $shengheAct, $openId, $shopId, $shId, $adminId, $adminName
        ){
            if ($shengheAct == 0){
                #step one 写入临时积分日志到正式积分日志表，并删除临时积分日志；返回临时积分总数
                $integral = $this->userIntegralAdd($shopId, $shId, $openId);
                if ($integral > 0) {
                    DpShangHuInfo::where('shId', $shId)
                                 ->increment('integralall', $integral);
                }
            }
            // 修改成员信息为通过审核
            DpShangHuInfo::where('shId', $shId)
                ->where('shopId', $shopId)
                ->update([
                    'shengheAct' => DpShangHuInfo::STATUS_PASS,
                    'shenheAdminId' => $adminId,
                    'shengheAdminName' => $adminName,
                ]);
            // 修改店铺状态为正常
            DpShopInfo::where('shopId', $shopId)->update(['state' => DpShopInfo::STATE_NORMAL]);
        });
    }

    /**
     * 会员临时积分日志转正式日志操作
     */
    protected function userIntegralAdd($shopId, $userId, $userOpenId)
    {
        // 查询注册(审核)前所获得的钻石数量
        $integralNum = DpUnregisterOpenidBinding::where('open_id', $userOpenId)
                                                ->where('privilege_type', 1)
                                                ->sum('num');
        if ($integralNum) {
            // 执行插入、删除
            \DB::connection('mysql_zdp_main')->transaction(function () use(
                $userOpenId, $userId, $shopId
            ){
                // 会员临时表
                DpUnregisteredUserInfos::where('we_chat_openid', $userOpenId)
                                       ->delete();
                // 查询钻石信息
                $uInfo = DpUnregisterOpenidBinding::where('open_id', $userOpenId)
                                                  ->where('privilege_type', 1)
                                                  ->first();
                // 将临时表记录写入正式表中
                DpIntegralChange::create([
                    'uid' => $userId,
                    'shopid' => $shopId,
                    'integral' => $uInfo->num,
                    'source' => $uInfo->privilege_mode,
                    'uip' => $uInfo->ip,
                    'adddate' => $uInfo->created_at,
                    'change_explain' => '审核前获得',
                ]);
                // 临时积分日志表
                DpUnregisterOpenidBinding::where('open_id', $userOpenId)
                                         ->where('privilege_type', 1)
                                         ->delete();
            });
        }

        return $integralNum;
    }

    /**
     * 拒绝店铺
     *
     * @param integer $shopId 店铺id
     * @param string  $reason 原因
     */
    public function refuse($shopId, $reason)
    {
        /** @var $auth Guard */
        $auth = \App::make(Guard::class);
        /** @var User $user */
        $user = $auth->user();
        $adminId = $user->id;
        $adminName = $user->user_name;

        //获取商户和店铺信息
        $shanghuInfo = DpShangHuInfo
            ::join('dp_shopInfo as shop', 'shop.shopId', '=', 'dp_shanghuInfo.shopId')
            ->where('dp_shanghuInfo.shopId', $shopId)
            ->where('dp_shanghuInfo.laobanHao', 0)
            ->select([
                'dp_shanghuInfo.shId',
                'dp_shanghuInfo.OpenID',
                'dp_shanghuInfo.xingming',
                'dp_shanghuInfo.lianxiTel',
                'shop.dianPuName',
            ])
            ->first();

        $time = $time = date("Y-m-d H:i:s", time());
        \DB::connection('mysql_zdp_main')->transaction(function () use (
            $time,$shanghuInfo,$adminId,$adminName,$shopId,$reason
        ){
            // 改变用户状态为拒绝
            DpShangHuInfo::where('shId', $shanghuInfo->shId)
                         ->update([
                             'grounds'          => $reason,
                             'shengheAct'       => DpShangHuInfo::STATUS_REFUSE,
                             'updatetime'       => $time,
                             'shenheAdminId'    => $adminId,
                             'shengheAdminName' => $adminName,
                         ]);
            // 店铺状态改为非正常
            DpShopInfo::where('shopId', $shopId)
                ->update(['state' => DpShopInfo::STATE_DEL]);
            $openid = $shanghuInfo->OpenID;//openid
            //点击消息需转到的链接地址
            $url = env('OPERATE_AUDIT_NOTICE');
            $title = '您好，抱歉您的资料因' . $reason . '未通过审核';//消息标题
            $msgName = $shanghuInfo->xingming . "店铺名：" .
                       $shanghuInfo->dianPuName;//店铺名字
            $msgPhone = $shanghuInfo->lianxiTel; //联系电话
            $msgTime = date('y-m-d h:i', time());//消息时间
            $remark = '感谢您的使用,如有疑问请拨打' . '028-85171136';//消息脚注
            $data = [
                "OpenID"   => $openid,
                'hostUrl'  => $url,
                'first'    => $title,
                'keyword1' => $msgName,
                'keyword2' => $msgPhone,
                'keyword3' => $msgTime,
                'remark'   => $remark,
            ];
            $data = base64_encode(json_encode($data));
            $sendWxMsg = \App::make(\App\Services\SendWeChatMessageService::class);
            $sendWxMsg->sendShopPassNotice($data);//调用微信推送模板
        });
    }
}