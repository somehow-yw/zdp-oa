<?php

Route::group(
    [
        'prefix'     => 'provider',
        'middleware' => ['auth'],
    ],
    function () {
        // 服务商相关
        Route::group(
            [],
            function () {
                // 获取服务商列表
                Route::get('/', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@index',
                    'permissions' => ['sp_list'],
                ]);
                // 查看服务商信息
                Route::get('show', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@show',
                    'permissions' => ['sp_show'],
                ]);
                // 测试导出
//                Route::get('order/export','ProviderController@export');
                // 服务商订单导出(旗盛兴)
                Route::post('order/export', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@export',
                    'permissions' => ['sp_order_export'],
                ]
            );
                // 服务商搜索
                Route::post('search', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@search',
                    'permissions' => ['sp_search'],
                ]);
                // 服务商搜索提示
                Route::post('hint', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@hint',
                    'permissions' => [
                        'sp_search',
                        'bi.provider',
                        'bi.provider.list',
                    ],
                ]);
                // 服务商客户分类列表
                Route::get('sort', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@sort',
                    'permissions' => ['sp_sort_list'],
                ]);
                // 服务商客户分类添加
                Route::post('sort/add', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@sortAdd',
                    'permissions' => ['sp_sort_add'],
                ]);
                // 服务商通过申请/关闭
                Route::post('handle', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@update',
                    'permissions' => ['sp_handle'],
                ]);
                // 服务商删除
                Route::post('del', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@del',
                    'permissions' => ['sp_handle', 'sp_del'],
                ]);
                // 服务商操作日志
                Route::get('log', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@log',
                    'permissions' => ['sp_log_list'],
                ]);
                // 服务商微信配置
                Route::post('wechat-config', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@updateWeChatConfig',
                    'permissions' => ['sp_wechat_config'],
                ]);
                // 服务商微信标签与菜单初始化
                Route::post('wechat-init', [
                    'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@initWechatTagAndMenu',
                    'permissions' => ['sp_wechat_config'],
                ]);

                // ================
                //  服务商信息更改
                // ================
                Route::post('sp/update', [
                    //'middleware'  => 'permissions',
                    'uses'        => 'ProviderController@updateSpInfo',
                    'permissions' => ['sp_info_update'],
                ]);

                // ============
                //  服务商微信用户标签管理
                // ============
                // 获取服务商微信用户标签配置
                Route::get('wechat-tags', 'ProviderController@getWechatTags');
                // 更改服务商微信用户标签名
                Route::post('wechat-tag/update',
                    'ProviderController@updateWechatTag');
                // 删除服务商微信用户标签
                Route::post('wechat-tag/del',
                    'ProviderController@delWechatTag');

                // ============
                //  服务商微信菜单管理
                // ============
                // 获取服务商微信菜单配置
                Route::get('wechat-menus', 'ProviderController@getWechatMenus');
                // 更改服务商微信菜单配置
                Route::post('wechat-menu/edit',
                    'ProviderController@editWechatMenu');
                // 删除服务商微信菜单配置
                Route::post('wechat-menu/del',
                    'ProviderController@delWechatMenu');
                // 获取服务商微信菜单类型
                Route::get('wechat-menu-types',
                    'ProviderController@getWechatMenuTypes');

                // 获取区域信息
                Route::get('province', 'ProviderController@getProvinces');
                Route::get('children/{id}', 'ProviderController@getChildren')
                     ->where('id', '[0-9]+');

                // 服务商区域配置
                Route::get('area', [
                    'middleware'  => 'permissions',
                    'uses'        => 'AreaController@get',
                    'permissions' => ['sp_info_update'],
                ]);
                Route::post('area/add', [
                    'middleware'  => 'permissions',
                    'uses'        => 'AreaController@add',
                    'permissions' => ['sp_info_update'],
                ]);
                Route::post('area/remove', [
                    'middleware'  => 'permissions',
                    'uses'        => 'AreaController@remove',
                    'permissions' => ['sp_info_update'],
                ]);
                Route::post('area/edit', [
                    'middleware'  => 'permissions',
                    'uses'        => 'AreaController@edit',
                    'permissions' => ['sp_info_update'],
                ]);
            }
        );
    }
);
