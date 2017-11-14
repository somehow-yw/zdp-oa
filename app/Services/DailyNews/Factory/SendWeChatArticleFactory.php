<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/1
 * Time: 18:11
 */

namespace App\Services\DailyNews\Factory;

use Illuminate\Foundation\Bus\DispatchesJobs;

use App\Jobs\SendSmsRemind;
use App\Jobs\SendWeChatDailyNews;

class SendWeChatArticleFactory
{
    use DispatchesJobs;

    // 根据数据内容加入数据到队列
    public function getSendObj($editIdArr, $reviewIdArr, $sendAreaIdArr)
    {
        if (count($editIdArr)) {
            echo '--发短信给编辑员' . PHP_EOL;
            $smsType = 'editPersonnel';     // 表示编辑人员
            $this->dispatch(new SendSmsRemind($editIdArr, $smsType));
        } elseif (count($reviewIdArr)) {
            echo '--发短信给审核员' . PHP_EOL;
            $smsType = 'reviewPersonnel';     // 表示审核人员
            $this->dispatch(new SendSmsRemind($reviewIdArr, $smsType));
        } elseif (count($sendAreaIdArr)) {
            echo '--文章推送' . PHP_EOL;
            $this->dispatch(new SendWeChatDailyNews($sendAreaIdArr));
        } else {
            echo '--已退出' . PHP_EOL;
            exit;
        }
    }
}