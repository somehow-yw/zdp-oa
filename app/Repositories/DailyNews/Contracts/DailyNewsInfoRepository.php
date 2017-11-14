<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/29
 * Time: 14:15
 */

namespace App\Repositories\DailyNews\Contracts;


interface DailyNewsInfoRepository
{
    /**
     * 今日推送文章查询
     *
     * @param int $areaId 大区ID
     *
     * @return object
     */
    public function getTodayArticle($areaId);

    /**
     * 是否存在对应的今日推文
     *
     * @param int $areaId 大区ID
     *
     * @return int
     */
    public function getTodayArticleTotal($areaId);

    /**
     * 每日推文类型获取
     *
     * @return array
     */
    public function getDailyNewsTypeList();

    /**
     * 添加每日推文
     *
     * @param array $addArr 推文内容 多维数组 结构详见对应MODEL
     *
     * @return void
     */
    public function addTodayArticle($addArr);

    /**
     * 修改每日推文
     *
     * @param int    $articleId    文章ID
     * @param int    $articleType  文章类型
     * @param string $articleTitle 文章标题
     * @param string $description  文章描述
     * @param string $articleImage 文章图片地址
     * @param int    $articleOrder 文章排列顺序
     *
     * @return void
     */
    public function updateArticle(
        $articleId, $articleType, $articleTitle,
        $description, $articleImage, $articleOrder
    );

    /**
     * 今日推文删除
     *
     * @param int $articleId 文章ID
     *
     * @return array
     */
    public function delTodayArticle($articleId);

    /**
     * 按文章类型取得今日文章内容
     *
     * @param int $articleType 文章类型
     *
     * @return object
     */
    public function getTodayArticleInfoByType($articleType);
}