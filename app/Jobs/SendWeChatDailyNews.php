<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Services\SendWeChatMessageService;

class SendWeChatDailyNews extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $messageSendAreaId;

    /**
     * Create a new job instance.
     * SendWeChatDailyNews constructor.
     *
     * @param array $messageSendAreaId 发送文章的大区ID 格式：[2,3,4]
     */
    public function __construct($messageSendAreaId)
    {
        $this->messageSendAreaId = $messageSendAreaId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SendWeChatMessageService $sendWeChatMessageService)
    {
        $sendWeChatMessageService->sendMessage($this->messageSendAreaId);
    }
}
