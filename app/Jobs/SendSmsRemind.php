<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Services\SendSmsMessageService;

class SendSmsRemind extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $messageReceiver;
    private $smsType;

    /**
     * Create a new job instance.
     * SendSmsRemind constructor.
     *
     * @param array  $messageReceiver 消息接收者 格式：[1,2,3]
     * @param string $smsType         消息接收者类型
     */
    public function __construct($messageReceiver, $smsType)
    {
        $this->messageReceiver = $messageReceiver;
        $this->smsType = $smsType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SendSmsMessageService $sendSmsMessageService)
    {
        $smsTxtArr = [];
        if ($this->smsType == 'editPersonnel') {
            // 每日文章编辑者
            $smsTxtArr = ['每日推送'];
        } elseif ($this->smsType == 'reviewPersonnel') {
            // 每日文章审核者
            $smsTxtArr = ['每日推送'];
        }
        $sendSmsMessageService->sendMessage($this->messageReceiver, $smsTxtArr);
    }
}
