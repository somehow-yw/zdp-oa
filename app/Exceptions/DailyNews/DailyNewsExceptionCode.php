<?php

namespace App\Exceptions\DailyNews;

/**
 * DailyNews exception code definitions
 */
final class DailyNewsExceptionCode
{
    /**
     * 记录不存在
     */
    const NOT_RECORD = 101;

    /**
     * 涨跌榜商品列表时，价格变化为空
     */
    const PRICE_CHANGE_NOT = 102;

    /**
     * 每日推送类型不正确
     */
    const DAILY_NEWS_TYPE_ERROR = 103;

    /**
     * 每日推送商品查询中涨跌榜查询时参数错误
     */
    const RISE_DECLINE_REQUEST_ERROR = 104;

    /**
     * 团购商品不可做屏蔽操作
     */
    const BULK_PURCHASING_SHIELD_NOT = 105;

    /**
     * 管理信息不存在记录不存在
     */
    const MANAGE_NOT_RECORD = 106;

    /**
     * 短信提醒消息发送失败
     */
    const SMS_SEND_FAILURE = 107;

    /**
     * 每日推送文章重复添加
     */
    const REPEAT_ADD_ARTICLE = 108;

    /**
     * 推荐商品已经存在
     */
    const RECOMMEND_GOODS_EXIST = 109;
}
