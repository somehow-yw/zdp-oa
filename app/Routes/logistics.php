<?php

Route::group(
    [
        'prefix'     => 'logistics',
        'middleware' => ['auth'],
        'namespace'  => '\Zdp\Logistics\Http\Controllers',
    ],
    function () {
        // 运力管理
        Route::group(
            [
                'prefix'      => 'capacity',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.capacity'],
            ],
            function () {
                Route::get('{display?}', 'Capacity@index'); // 运力列表(可选择详细或简要)
                Route::post('update', 'Capacity@update');   // 运力修改
                Route::post('delete', 'Capacity@delete');   // 运力删除
                Route::post('add', 'Capacity@add');         // 运力添加
            }
        );
        // 司机管理
        Route::group(
            [
                'prefix'      => 'driver',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.driver'],
            ],
            function () {
                Route::get('', 'Driver@index');             // 司机列表
                Route::post('update', 'Driver@update');     // 司机信息修改
                Route::post('status', 'Driver@status');     // 司机状态修改
                Route::post('delete', 'Driver@delete');     // 司机删除
                Route::get('available', 'Delivery@driver'); // 可用司机

                Route::get('complain', 'Driver@complain'); // 司机投诉
                Route::get('complain/stat', 'Driver@complainStat'); // 司机投诉统计
            }
        );
        // 运单管理
        Route::group(
            [
                'prefix'      => 'delivery',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.delivery'],
            ],
            function () {
                // 获取当日所有运单
                Route::get('points', 'Delivery@points');
                // 当日统计信息
                Route::get('statics', 'Delivery@statics');
                // 分配运单
                Route::post('assign', 'Delivery@assign');
                // 运单列表
                Route::post('', 'Delivery@index');
                // 运单详情
                Route::get('{id}', 'Delivery@show');
                // 运单详情页面(司机/车辆/发货人/收货人)保存信息
                Route::post('update/{method}/{id}', 'Delivery@update');
                // 确认揽收
                Route::post('received/{id}', 'Delivery@received');
                // 废弃操作
                Route::post('cancel/{id}', 'Delivery@cancel');
                // 运单导入
                Route::post('import', 'Delivery@import');
                //
                Route::get('import/result/{name}', function ($name) {
                    $path = storage_path('excel/exports/' . $name);

                    return response()->download($path);
                });

                // 导出当前筛选项的运单
                Route::get('export', 'Delivery@export');
            }
        );
        // 客户管理
        Route::group(
            [
                'prefix'      => 'custom',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.custom'],
            ],
            function () {
                Route::get('', 'Custom@index');     // 获取列表
                Route::get('{id}', 'Custom@view');  // 收货人详情
                Route::post('{id}', 'Custom@edit'); // 修改收货人信息
            }
        );
        // 物流区域配置
        Route::group(
            [
                'prefix'      => 'area',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.area'],
            ],
            function () {
                // 获取某区域的上级链以及所有下级
                Route::get('map/{area_id?}', 'Area@map');
                // 修改街道状态
                Route::post('street/status', 'Area@status');
                // 获取某个区域内的街道列表
                Route::get('street/{area_id?}', 'Area@street');
            }
        );
        // 运单实时动态
        Route::group(
            [
                'prefix'      => 'intime',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.intime'],
            ],
            function () {
                Route::get('', 'InTime@index');     // 运单列表
                Route::get('{id}', 'InTime@show');  // 运单详情
                Route::get('sms/delivery/{id}', 'InTime@deliverySMS'); // 发送发件提醒
            }
        );
        // 商户管理
        Route::group(
            [
                'prefix'      => 'shop',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.shop'],
            ],
            function () {
                Route::get('search', 'Shop@search');        // 搜索商户(店铺)
                Route::post('sign', 'Shop@sign');           // 商户注册
                Route::post('order/{id}', 'Shop@create');   // 后台录单
                // 后台管理地址
                Route::group(
                    [
                        'prefix' => 'address',
                    ],
                    function () {
                        Route::get('{id}', 'Shop@get');             // 获取收货地址列表
                        Route::post('update/{id}', 'Shop@update');  // 更改收货地址
                        Route::post('add/{id}', 'Shop@add');        // 添加收货地址
                    }
                );
            }
        );
        // 运费管理
        Route::group(
            [
                'prefix'      => 'charge',
                'middleware'  => ['permissions'],
                'permissions' => ['logistics.charge'],
            ],
            function () {
                Route::get('freed', 'Charge@freed');         // 已作废(免单)列表
                Route::get('unpaid', 'Charge@unpaid');       // 未收款列表
                Route::get('paid', 'Charge@paid');           // 已收款列表
                Route::get('canceled', 'Charge@canceled');   // 已撤销列表

                Route::get('paid/export', 'Charge@export');  // 导出已收款列表

                Route::post('create', 'Charge@create');      // 新增收款
                Route::post('cancel/{id}', 'Charge@cancel'); // 撤销收款
                Route::post('free', 'Charge@free');          // 免单
            }
        );
    }
);

// 地址搜索相关
Route::group(
    [
        'middleware'  => ['auth', 'permissions'],
        'permissions' => ['logistics.address'],
        'namespace'   => '\Zdp\Logistics\Http\Controllers',
    ],
    function () {
        Route::post('map/search/{method}', 'Map@search');
        Route::post('map/geocode/regeo', 'Map@geocode');
    }
);

Route::group(
    [
        'namespace'  => 'Zdp\Mobile\Http\Controllers',
        'middleware' => ['auth'],
    ],
    function () {
        // 手机验证码
        Route::post('verify/mobile', 'Verify@mobile');
    }
);
