<?php

Route::group(
    [
        'prefix' => 'bi',
        'middleware' => ['auth'],
        'namespace' => '\Zdp\BI\Http\Controllers',
    ],
    function () {
        Route::post('order', 'Order@index');            // 订单统计入口
        Route::post('order/filter', 'Order@filter');    // 获取订单筛选项

        Route::get('order/Statistics/transform', 'OrderStatistics@transform'); //统计订单流失率
        Route::get('order/Statistics/refund/operation', 'OrderStatistics@refundOperation');//统计退款运营介入率
        Route::get('order/Statistics/remind/operation', 'OrderStatistics@remindOperation');//统计催货运营介入
        Route::get('order/Statistics/cancel', 'OrderStatistics@abnormalCancel');//买家取消订单理由统计

        Route::post('groupon', 'GroupOn@index'); // 集中采购商品统计
        Route::post('groupon/rank', 'GroupOn@rank');  // 集中采购排行榜

        Route::post('goods', 'Goods@index');             // 商品分布状况
        Route::post('goods/trend', 'Goods@trend');       // 商品趋势
        Route::post('goods/filter', 'Goods@filter');     // 获得统计筛选项
        Route::post('goods/series', 'Goods@series');     // 筛选项中根据省市获取对应市场信息

        Route::post('call', 'Call@index');             // 商品咨询统计入口
        Route::post('call/filter', 'Call@filter');     // 获得咨询统计筛选项
        Route::post('call/series', 'Call@series');   // 筛选项中省市县级联关系
        Route::post('call/rank', 'Call@rank');         // 咨询统计排行

        // ====================
        //  店铺统计
        // ====================
        Route::group([
            'prefix' => 'shop',
        ], function () {
            Route::post('filter', 'Shop@filter');   // 店铺统计获取筛选项接口
            Route::post('series', 'Shop@series');   // 根据省市获取关联市场/区域信息
            Route::post('histogram', 'Shop@total');   // 店铺统计柱状图总计接口
            Route::post('chart', 'Shop@detail');   // 店铺统计折线图接口
            Route::post('rank', 'Shop@rank');   // 店铺排名接口
        });

        // ====================
        //  销量排行
        // ====================
        Route::group([
            'prefix' => 'sale',
        ], function () {
            Route::post('', 'SaleRank@saleRank');   // 买家及区域排行接口
            Route::post('filter', 'SaleRank@saleFilter');   // 销售排名筛选项接口
            Route::post('goods', 'SaleRank@grank');   // 商品排名接口
        });

        // ====================
        //  服务商管理 统计相关
        // ====================
        Route::group([
            'prefix' => 'provider',
            'middleware' => 'permissions',
            'permissions' => ['bi.provider', 'bi.provider.list'],
        ], function () {
            // 服务商列表
            Route::get('', 'ProviderStats@index');
            // 获取筛选项
            Route::post('filter', 'ProviderStats@filter');
            // 客户统计
            Route::post('customers', 'ProviderStats@customers');
            //客户注册统计
            Route::get('customersRegister', 'ProviderStats@customersRegister');
            // 订单统计
            Route::post('order', 'ProviderStats@order');
            // 订单排行
            Route::post('rank-order', 'ProviderStats@orank');
            // 商品排行
            Route::post('rank-goods', 'ProviderStats@grank');
        });

        // ==========================
        //  白条支付 冻品贷 相关统计
        // ==========================
        Route::group([
            'prefix' => 'loans'
        ], function () {
            // 筛选项获取
            Route::post('filter', 'Loans@filter');
            // 用户分析
            Route::post('user/total', 'Loans@total');
            Route::post('user/trend', 'Loans@trend');
            Route::post('user/layout', 'Loans@customLayout');
            // 支付行为分析
            Route::post('payment/trend', 'Loans@paymentTrend');
            Route::post('payment/layout', 'Loans@paymentLayout');
        });

        Route::group([
            'prefix' => 'appraise'
        ], function () {
            Route::get('seller', 'AppraiseStatistics@seller');
            Route::get('seller/all', 'AppraiseStatistics@sellerAll');
            Route::get('buyer', 'AppraiseStatistics@buyer');
            Route::get('search', 'AppraiseStatistics@search');
            Route::get('goods/info','AppraiseStatistics@goods');
        });

    }
);