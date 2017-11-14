<?php
/**
 * Created by PhpStorm.
 * User: Chen
 * Date: 2017/10/27
 * Time: 15:00
 */

namespace App\Workflows;

use App\Jobs\SendWechatToSp;
use App\Models\DpMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Zdp\Main\Data\Models\DpShangHuInfo;
use Zdp\ServiceProvider\Data\Models\User;
use Zdp\WechatJob\Services\WechatJobService;

class TicklWorkflow
{

    private $weChatSend;

    public function __construct(
        WechatJobService $wechatJobService
    )
    {
        $this->weChatSend = $wechatJobService;
    }

    /**
     * 处理反馈
     *
     * @param $tickingId  反馈id
     * @param $content  反馈内容
     * @param int $type 反馈来源
     */
    public function rePlay($tickingId, $content, $type)
    {
        $user = \Auth::user();
        if (empty($user)) {
            throw new \Exception('登录已过期，请重新登录！');
        }
        $userName = $user->user_name;
        $query = DpMessage::query()
            ->where('id', $tickingId);
        $userQuery = clone $query;
        $userId = $userQuery->first()->shid;
        DB::transaction(function () use ($userName, $tickingId, $content, $type, $query, $userId) {
            if ($type == DpMessage::ZDP_MESSAGE) {
                $query->update(
                    [
                        'msgact' => DpMessage::MSGACT_OK,
                        'ope_name' => $userName,
                        'yijian' => $content
                    ]
                );
                $this->replyByZdongpin($userId, $this->sendMessage($content), $type);
            } elseif ($type == DpMessage::SP_MESSAGE) {
                $this->replyBySp($userId);
                $this->replyByUser($userId, $content);
            }
        });
    }

    /**
     * 给找冻品网用户回复模板消息
     *
     * @param $tickingId
     * @param array $reData
     * @param $type
     */
    public function replyByZdongpin($tickingId, $reData = [], $type)
    {
        $OpenId = $this->getOpenId($tickingId, $type);
        $this->weChatSend->sendTemplateNotify(
            $OpenId,
            null,
            $reData,
            null,
            config('wechat_template.ZdpReplyTicking.mini_template_id')
        );

    }

    /**
     * 给服务商回复模板消息
     */
    public function replyBySp($userId)
    {
        $prefix = User::query()
            ->leftjoin('wechat_accounts as s', 'users.sp_id', '=', 's.sp_id')
            ->where('users.id', '=', $userId)
            ->value('s.source');
        $url = config('sp_wechat_template.replyToSp.url');
        if (empty($prefix)) {
            throw new \Exception('服务商不存在');
        }
        $data = [
            'first' => ['您客户反馈的功能操作问题，平台已受理', '#173177'],
            'keyword1' => ['已受理', '#173177'],
            'keyword2' => [Carbon::now()->format('Y-m-d H:i:s'), '#173177'],
            'remark' => ['感谢您的反馈', '#173177'],
        ];
        $job = new SendWechatToSp($prefix, $url, config('sp_wechat_template.replyToSp'), $data);
        dispatch($job);
    }

    public function replyByUser($userId, $content)
    {
        $user = User::query()
            ->leftjoin('wechat_accounts as s', 'users.sp_id', '=', 's.sp_id')
            ->where('users.id', '=', $userId)
            ->select('s.source as source', 'users.wechat_openid as openId')
            ->first();
        $url = config('sp_wechat_template.replyToUser.url');
        $prefix = $user->source;
        if (empty($prefix)) {
            throw new \Exception('服务商不存在');
        }
        $data = [
            'first' => ["平台回复: $content",'#173177'],
            'keyword1' => ['已受理','#173177'],
            'keyword2' => [Carbon::now()->format('Y-m-d H:i:s'),'#173177'],
            'remark' => ['请悉知','#173177']
        ];
        $job = new SendWechatToSp($prefix, $url, config('sp_wechat_template.replyToSp'), $data, $user->openId);
        dispatch($job);
    }

    /**
     * 获取用户openid
     *
     * @param $userId
     * @param $type
     * @return mixed
     */
    private function getOpenId($userId, $type)
    {
        $openId = DpShangHuInfo::query()
            ->where('shId', $userId)
            ->value('OpenID');
        return $openId;
    }


    public function sendMessage($content)
    {
        return [
            'first' => ['value' => '平台回复:' . $content, 'color' => '#173177'],
            'keyword1' => ['value' => '已受理', 'color' => '#173177'],
            'keyword2' => ['value' => '受理时间:' . date("m月d日"), 'color' => '#173177'],
            'remark' => ['value' => '感谢您的反馈。', 'color' => '#173177'],
        ];
    }
}