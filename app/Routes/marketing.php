<?php
/**
 * 营销中心路由
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 10:49
 */

Route::group(
    [
        'prefix'      => 'marketing',
        'middleware'  => ['auth', 'permissions'],
        'permissions' => ['marketing'],
    ],
    function () {
        // 兑换券管理
        Route::group(
            [
                'prefix'      => 'exchange-ticket',
                'middleware'  => 'permissions',
                'permissions' => ['marketing', 'exchange_ticket'],
            ],
            function () {
                // 兑换券分类获取
                Route::get('type/list', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ExchangeTicketController@getType',
                    'permissions' => ['marketing', 'exchange_ticket_type_list'],
                ]);
                // 兑换券添加
                Route::post('add', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ExchangeTicketController@add',
                    'permissions' => ['marketing', 'exchange_ticket_add'],
                ]);
                // 兑换券上、下架
                Route::post('sell-status/update', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ExchangeTicketController@onSell',
                    'permissions' => ['marketing', 'exchange_ticket_sell_status'],
                ]);
                // 兑换券查询
                Route::get('list', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ExchangeTicketController@getList',
                    'permissions' => ['marketing', 'exchange_ticket_list'],
                ]);
                // 兑换券购买记录查询
                Route::get('buy/list', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ExchangeTicketController@buyList',
                    'permissions' => ['marketing', 'exchange_ticket_buy_list'],
                ]);
                // 兑换券购买记录状态更改
                Route::post('buy-log/status/update', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ExchangeTicketController@updateExchangeStatus',
                    'permissions' => ['marketing', 'exchange_ticket_buy_use_status'],
                ]);
            }
        );
    }
);
