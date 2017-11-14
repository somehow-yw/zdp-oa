<?php
/**
 * Created by PhpStorm.
 * User: Chen
 * Date: 2017/11/10
 * Time: 10:37
 */
return [
    'ZdpReplyTicking' => [
        'mini_template_id' => 'OPENTM410086598',
        // 模板名称
        'name'      => '找冻品网回复用户反馈',
        // 消息点击后跳转到的URL地址
        'link_urls' => [
            // 买家订单详情页
            'buyer_order_info'  => '',
            // 卖家订单详情页
            'seller_order_info' => '',
        ],
        // 消息结构体
        'data'      => [
            'first' => ['value' => '平台回复:','color' => '#173177'],
            'keyword1' => ['value' => '已受理','color' => '#173177'],
            'keyword2' => ['value' => '受理时间:' . date("m月d日"),'color' => '#173177'],
            'remark' => ['value' => '感谢您的反馈。','color' => '#173177'],
        ],
    ],
];