<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\Shops\MemberService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 调整会员的微信分组
 * Class MemberWeChatGroupAdjust
 * @package App\Jobs
 */
class MemberWeChatGroupAdjust extends Job implements SelfHandling, ShouldQueue
{
    protected $setUserGroupArr;

    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     * MemberWeChatGroupAdjust constructor.
     *
     * @param array $setUserGroupArr array 微信分组调整信息 ['openid'=>会员微信OPENID, 'to_groupid'=>分组ID]
     */
    public function __construct(array $setUserGroupArr)
    {
        $this->setUserGroupArr = $setUserGroupArr;
    }

    /**
     * Execute the job.
     *
     * @param MemberService $memberService
     */
    public function handle(MemberService $memberService)
    {
        $memberService->memberWweChatGroup($this->setUserGroupArr);
    }
}
