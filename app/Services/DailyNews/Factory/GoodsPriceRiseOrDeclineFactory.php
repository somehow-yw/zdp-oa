<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/31
 * Time: 11:14
 */

namespace App\Services\DailyNews\Factory;

use App;

use App\Services\DailyNews\Contracts\AbstractGoodsPriceRiseOrDecline;

use App\Services\DailyNews\Module\RiseGoods;
use App\Services\DailyNews\Module\DeclineGoods;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;

class GoodsPriceRiseOrDeclineFactory
{
    /**
     * 返回涨跌榜商品处理实例
     *
     * @param string $priceChange 涨价或降价标识
     *
     * @return object
     * @throws AppException
     */
    public static function getPriceRiseOrDeclineObj($priceChange)
    {
        switch ($priceChange) {
            case 'rise':
                // 涨价
                App::singleton(AbstractGoodsPriceRiseOrDecline::class, RiseGoods::class);
                break;
            case 'decline':
                // 降价
                App::singleton(AbstractGoodsPriceRiseOrDecline::class, DeclineGoods::class);
                break;
            default:
                // 参数错误
                throw new AppException('价格变化参数错误', DailyNewsExceptionCode::RISE_DECLINE_REQUEST_ERROR);
        }

        return App::make(AbstractGoodsPriceRiseOrDecline::class);
    }
}