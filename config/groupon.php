<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26 0026
 * Time: 下午 4:44
 */

return [
    // 微信模板配置
    'wechat' => [
        'seller' => env('QUOTED_PRICE_NOTICE', 'BuCSrIZqsicLnN5ug05PZmmJGNV_dP4tQimC4Rf7Rb4'),
        'buyer'  => env('PAYMENT_NOTICE', 'jfK94FuujwMsjEAxEYfbb3Vls8Iw81yKAtqMGGJVYQw'),
        'remind_seller' => env('ORDER_DELIVERY', 'dZN-2VmzTFeLayVGyQPT_gGpsslGcgYgoAcsUfksKOo'),
        'url'    => [
            'seller' => env('WECHAT_REQUEST_URL') .
                        '/?m=PublicTemplate&c=ApiPublic&a=grouponOfferPrice',
            'buyer'  => env('WECHAT_REQUEST_URL') .
                        '/?m=PublicTemplate&c=ApiPublic&a=buyerOrder',
            'remindSeller' => env('WECHAT_REQUEST_URL').
                              '/seller-client/order_detail/',
            'afterSale' => env('WECHAT_REQUEST_URL').
                           '/seller-client/order_detail/',
            'buyerOrderDetail' => env('WECHAT_REQUEST_URL').
                                  '/buyer-client/order_detail/',
        ],
    ],
];