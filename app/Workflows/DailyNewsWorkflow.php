<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/30
 * Time: 15:53
 */

namespace App\Workflows;

use App\Services\DailyNewsService;

use App\Models\DpDailyNewsInfo;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;

class DailyNewsWorkflow
{
    private $dailyNewsService;

    public function __construct(
        DailyNewsService $dailyNewsService
    ) {
        $this->dailyNewsService = $dailyNewsService;
    }

    /**
     * 今日推送商品查询
     *
     * @param int    $areaId        大区ID
     * @param int    $articleTypeId 推文类型
     * @param int    $page          当前页数
     * @param int    $size          获取数据量
     * @param string $priceChange   涨跌榜数据获取时 价格变化 涨='Rise' 跌='fall' 涨跌榜查询必须
     *
     * @return array|void
     * @throws AppException
     */
    public function getTodaySendGoodsList($areaId, $articleTypeId, $page, $size, $priceChange)
    {
        switch ($articleTypeId) {
            case DpDailyNewsInfo::BULK_PURCHASING:
                // 团购
                $reDataArr = $this->dailyNewsService->getBulkPurchasingGoods($areaId, $page, $size);
                break;
            case DpDailyNewsInfo::NEW_PRODUCT:
                // 新品
            case DpDailyNewsInfo::HOT_SALE:
                // 热门
                $reDataArr = $this->dailyNewsService
                    ->getNewProductOrHotSaleGoods($areaId, $articleTypeId, $page, $size);
                break;
            case DpDailyNewsInfo::RECOMMEND_GOODS:
                // 商品推荐榜
                $reDataArr = $this->dailyNewsService
                    ->getRecommendGoods($areaId, $page, $size);
                break;
            case DpDailyNewsInfo::PRICE_LIST:
                // 涨跌榜
                if (empty($priceChange)) {
                    throw new AppException('所查询的价格变化必须有', DailyNewsExceptionCode::PRICE_CHANGE_NOT);
                }
                $reDataArr = $this->dailyNewsService->getPriceRiseOrDeclineGoods($areaId, $priceChange, $page, $size);
                break;
            default:
                // 错误
                throw new AppException('每日推送类型不正确', DailyNewsExceptionCode::DAILY_NEWS_TYPE_ERROR);
                break;
        }

        return $reDataArr;
    }

    /**
     * 今日推送商品屏蔽操作
     *
     * @param int    $id            操作ID
     * @param int    $goodsId       商品ID
     * @param int    $articleTypeId 文章类型
     * @param int    $shieldStatus  屏蔽操作类型
     * @param string $priceChange   涨价或降价标识
     *
     * @return array
     * @throws AppException
     */
    public function shieldTodaySendGoods($id, $goodsId, $articleTypeId, $shieldStatus, $priceChange)
    {
        switch ($articleTypeId) {
            case DpDailyNewsInfo::BULK_PURCHASING:
                // 团购
                throw new AppException('团购商品不可做屏蔽操作', DailyNewsExceptionCode::BULK_PURCHASING_SHIELD_NOT);
                break;
            case DpDailyNewsInfo::NEW_PRODUCT:
                // 新品
            case DpDailyNewsInfo::HOT_SALE:
                // 热门
                $reDataArr = $this->dailyNewsService
                    ->shieldNewProductOrHotSaleGoods($id, $goodsId, $shieldStatus);
                break;
            case DpDailyNewsInfo::PRICE_LIST:
                // 涨跌榜
                if (empty($priceChange)) {
                    throw new AppException('所查询的价格变化必须有', DailyNewsExceptionCode::PRICE_CHANGE_NOT);
                }
                $reDataArr =
                    $this->dailyNewsService->shieldPriceRiseOrDeclineGoods($id, $goodsId, $shieldStatus, $priceChange);
                break;
            default:
                // 错误
                throw new AppException('每日推送类型不正确', DailyNewsExceptionCode::DAILY_NEWS_TYPE_ERROR);
                break;
        }

        return $reDataArr;
    }
}