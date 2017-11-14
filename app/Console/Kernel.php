<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,

        \App\Console\Commands\DailyNews\WeChatSendArticle::class,

        \Zdp\Search\Commands\Index\Init::class,
        \Zdp\Search\Commands\Dict\Init::class,

        \Zdp\BI\Commands\SyncOrder::class,
        \Zdp\BI\Commands\SyncGoods::class,
        \Zdp\BI\Commands\SyncCall::class,
        \Zdp\BI\Commands\SyncOrderStatistics::class,
        \Zdp\BI\Commands\SyncGoodsAppraise::class,
        \Zdp\BI\Commands\SyncShopAppraise::class,
        \Zdp\BI\Commands\SyncShop::class,

        // 服务商统计
        \Zdp\BI\Commands\SyncCustomerProvider::class,
        \Zdp\BI\Commands\SyncGoodsProvider::class,

        // 服务商订单系统导出要求上线，冻品贷统计延后上线，故注释
        // 冻品贷统计
        \Zdp\BI\Commands\SyncLoanUser::class,
        \Zdp\BI\Commands\SyncLoanPayment::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('bi:sync:order')
            ->everyTenMinutes();

        $schedule->command('bi:sync:goods:appraises')
            ->hourly();

        $schedule->command('bi:sync:shop')
            ->hourly();

        $schedule->command('bi:sync:shop:appraises')
            ->hourly();

        $schedule->command('bi:sync:orderStatistics')
            ->everyTenMinutes();

        // 每小时同步咨询
        $schedule->command('bi:sync:call')
            ->hourly();

        // 同步每天商品状态变化  代码中使用 yesterday的date进行记录，必须在凌晨跑
        $schedule->command('bi:sync:goods')
            ->dailyAt('00:10');

        // 同步服务商增加的客户
        $schedule->command('sp:sync-increment-customers-daily')
            ->dailyAt('02:00');
        // 同步服务商成交订单
        $schedule->command('sp:sync-order-goods-daily')
            ->dailyAt('03:00');

        // 服务商订单导出功能上线，徙木金融上线后，再取消注释
        // 冻品贷用户日志(时间最好在凌晨00:00-01:00)
        $schedule->command('loan:sync-user-daily')
            ->dailyAt('00:02');
        // 冻品贷订单统计
        $schedule->command('loan:sync-payment-daily')
            ->dailyAt('01:00');
    }
}
