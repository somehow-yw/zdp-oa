<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/29
 * Time: 14:16
 */

namespace app\Repositories\DailyNews;

use Carbon\Carbon;

use App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository as RepositoriesContract;

use App\Models\DpDailyNewsInfo;

use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;

class DailyNewsInfoRepository implements RepositoriesContract
{
    /**
     * 今日推送文章查询
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::getTodayArticle()
     *
     * @param int $areaId 大区ID
     *
     * @return object
     */
    public function getTodayArticle($areaId)
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        return DpDailyNewsInfo::where('area_id', $areaId)
            ->where('send_date', $todayDate)
            ->get();
    }

    /**
     * 是否存在对应的今日推文
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::getTodayArticleTotal()
     *
     * @param int $areaId 大区ID
     *
     * @return int
     */
    public function getTodayArticleTotal($areaId)
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        return DpDailyNewsInfo::where('area_id', $areaId)
            ->where('send_date', $todayDate)
            ->count();
    }

    /**
     * 每日推文类型获取
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::getDailyNewsTypeList()
     *
     * @return array
     */
    public function getDailyNewsTypeList()
    {
        return DpDailyNewsInfo::getShowTypeArr();
    }

    /**
     * 添加每日推文
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::addTodayArticle()
     *
     * @param array $addArr 推文内容 多维数组 结构详见对应MODEL
     *
     * @return void
     * @throws AppException
     */
    public function addTodayArticle($addArr)
    {
        DpDailyNewsInfo::insert($addArr);
    }

    /**
     * 修改每日推文
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::updateArticle()
     *
     * @param int    $articleId    文章ID
     * @param int    $articleType  文章类型
     * @param string $articleTitle 文章标题
     * @param string $description  文章描述
     * @param string $articleImage 文章图片地址
     * @param int    $articleOrder 文章排列顺序
     *
     * @return void
     * @throws AppException
     */
    public function updateArticle(
        $articleId, $articleType, $articleTitle,
        $description, $articleImage, $articleOrder
    ) {
        // 可修改状态
        $articleStatusArr = [
            DpDailyNewsInfo::NOT_AUDIT,
            DpDailyNewsInfo::PASS_AUDIT,
        ];
        $articleInfoObj = $this->getTodayArticleInfoById($articleId, $articleStatusArr);
        if ($articleInfoObj) {
            $articleInfoObj->news_type = $articleType;
            $articleInfoObj->news_title = $articleTitle;
            $articleInfoObj->news_description = $description;
            $articleInfoObj->news_images = $articleImage;
            $articleInfoObj->order_num = $articleOrder;
            $articleInfoObj->status = DpDailyNewsInfo::NOT_AUDIT;
            $articleInfoObj->save();
        } else {
            throw new AppException(sprintf('记录%d不存在', $articleId), DailyNewsExceptionCode::NOT_RECORD);
        }
    }

    /**
     * 今日推文删除
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::delTodayArticle()
     *
     * @param int $articleId 文章ID
     *
     * @return void
     * @throws AppException
     */
    public function delTodayArticle($articleId)
    {
        // 可删除状态
        $articleStatusArr = [
            DpDailyNewsInfo::NOT_AUDIT,
            DpDailyNewsInfo::PASS_AUDIT,
        ];
        $articleInfoObj = $this->getTodayArticleInfoById($articleId, $articleStatusArr);
        if ($articleInfoObj) {
            $articleInfoObj->delete();
        } else {
            throw new AppException(sprintf('记录%d不存在', $articleId), DailyNewsExceptionCode::NOT_RECORD);
        }
    }

    /**
     * 按文章类型取得今日文章内容
     *
     * @see \App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository::getTodayArticleInfoByType()
     *
     * @param int $articleType 文章类型
     *
     * @return object
     */
    public function getTodayArticleInfoByType($articleType)
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $articleInfo = DpDailyNewsInfo::where('news_type', $articleType)
            ->where('send_date', $todayDate)
            ->first();

        return $articleInfo;
    }

    /**
     * 按文章ID取得今日文章内容
     *
     * @param int   $articleId        文章ID
     * @param array $articleStatusArr 需满足的状态 格式如：[1,2,3]
     *
     * @return object
     */
    private function getTodayArticleInfoById($articleId, $articleStatusArr)
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $articleInfo = DpDailyNewsInfo::where('id', $articleId)
            ->whereIn('status', $articleStatusArr)
            ->where('send_date', $todayDate)
            ->first();

        return $articleInfo;
    }
}