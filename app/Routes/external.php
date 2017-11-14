<?php
/**
 * 外部供应链路由
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/8/16
 * Time: 14:30
 */

Route::group(
    [
        'prefix'      => 'external',
        'middleware'  => ['auth', 'permissions'],
        'permissions' => ['external'],
    ],
    function () {
        // 冻品白条管理
        Route::group(
            [
                'prefix'      => 'ious',
                'namespace'   => 'External\Ious',
                //'middleware'  => 'permissions',
                'permissions' => ['ious'],
            ],
            function () {
                //==============
                // 徙木冻品贷
                //==============
                // 白名单获取
                Route::get('ximu/list', [
                    //'middleware'  => 'permissions',
                    'uses'        => 'XimuIousController@getList',
                    'permissions' => ['ximu_ious_list'],
                ]);
            }
        );
    }
);
