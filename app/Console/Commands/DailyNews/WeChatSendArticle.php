<?php

namespace App\Console\Commands\DailyNews;

use Illuminate\Console\Command;

use App\Services\DailyNewsService;

class WeChatSendArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wechar:daily-article-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '微信对48小时内有互动的会员推送每日文章';
    protected $dailyNewsService;

    /**
     * Create a new command instance.
     *
     * @param DailyNewsService $dailyNewsService
     *
     * @return void
     */
    public function __construct(
        DailyNewsService $dailyNewsService
    ) {
        parent::__construct();

        $this->dailyNewsService = $dailyNewsService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dailyNewsService->sendWeChatDailyArticle();
    }
}
