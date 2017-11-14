<?php

namespace App\Jobs;

use App;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Services\SendSmsMessageService;
use App\Services\SendWeChatMessageService;

/**
 * Class SendGoodsOperateNotice.
 * 商品操作通知处理
 *
 * @package App\Jobs
 */
class SendGoodsOperateNotice extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $goodsId;
    private $refusedReason;
    private $noticeWay;

    /**
     * Create a new job instance.
     *
     * @param $goodsId       int 商品ID
     * @param $refusedReason string 操作备注
     * @param $noticeWay     int 操作通知卖家的类型 0=不进行通知 1=微信通知 2=短信通知
     */
    public function __construct($goodsId, $refusedReason, $noticeWay)
    {
        $this->goodsId = $goodsId;
        $this->refusedReason = $refusedReason;
        $this->noticeWay = $noticeWay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->noticeWay) {
            case 1:
                /** @var $sendClass SendWeChatMessageService */
                $sendClass = App::make(SendWeChatMessageService::class);
                break;
            case 2:
                /** @var $sendClass SendSmsMessageService */
                $sendClass = App::make(SendSmsMessageService::class);
                break;
            default:
                return;
        }
        $sendClass->sendAuditRefusedNotice($this->goodsId, $this->refusedReason);
    }
}
