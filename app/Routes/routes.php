<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// 不需做登录验证
// 登录页面
Route::get(
    '/',
    function () {
        return view('common.loginPage-html');
    }
);

Route::post('user/login', 'AuthController@login');        // 登录操作
Route::post('user/logout', 'AuthController@logout');      // 退出登录操作

// 需做登录验证t
Route::group(
    ['middleware' => ['auth']],
    function () {
        //=======================
        //  操作员及权限类
        //=======================
        Route::group(
            ['prefix' => 'user'],
            function () {
                // 当前操作员菜单导航及所拥有权限获取
                Route::get('navigate', 'PrivilegeController@getUserNavigate');
                // 操作员修改登录密码
                Route::post('password/update', 'UserController@updateUserPassword');
                // 登录成功后首页面
                Route::get('home', 'UserController@homeView');
                // 操作员列表
                Route::get(
                    'list',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@getUserList',
                        'permissions' => ['user_list', 'user'],
                    ]
                );
                // 操作员信息获取
                Route::get(
                    'info',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@getUserInfo',
                        'permissions' => [
                            'user_info',
                            'user_info_update',
                            'user',
                        ],
                    ]
                );
                // 添加操作员
                Route::post(
                    'add',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@addUser',
                        'permissions' => ['user_add'],
                    ]
                );
                // 操作员信息修改
                Route::post(
                    'info/update',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@updateUserInfo',
                        'permissions' => ['user_info_update'],
                    ]
                );
                // 操作员状态修改
                Route::post(
                    'status/update',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@updateUserStatus',
                        'permissions' => [
                            'user_status_update',
                            'user_status_update_delete',
                        ],
                    ]
                );
                // 操作员权限获取
                Route::get(
                    'privilege',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@getUserPrivilege',
                        'permissions' => [
                            'user_privilege_get',
                            'user_privilege_update',
                        ],
                    ]
                );
                // 操作员权限分配
                Route::post(
                    'privilege/update',
                    [
                        'middleware' => 'permissions',
                        'uses' => 'UserController@updateUserPrivilege',
                        'permissions' => ['user_privilege_update'],
                    ]
                );
            }
        );

        //=======================
        //  部门(组)管理类
        //=======================
        Route::group(
            ['prefix' => 'department'],
            function () {
                // 部门(组)添加
                Route::post(
                    'add',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['department_add'],
                        'uses' => 'DepartmentController@addDepartment',
                    ]
                );
                // 部门(组)列表
                Route::get(
                    'list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['department_list', 'department'],
                        'uses' => 'DepartmentController@getDepartmentList',
                    ]
                );
                // 部门(组)信息获取
                Route::get(
                    'info',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'department_info',
                            'department_info_update',
                        ],
                        'uses' => 'DepartmentController@getDepartmentInfo',
                    ]
                );
                // 部门(组)信息修改
                Route::post(
                    'info/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['department_info_update'],
                        'uses' => 'DepartmentController@updateDepartmentInfo',
                    ]
                );
                // 部门(组)状态更改操作
                Route::post(
                    'status/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['department_status_update'],
                        'uses' => 'DepartmentController@updateDepartmentStatus',
                    ]
                );
            }
        );

        //=======================
        //  超管ROOT权限管理类
        //=======================
        Route::group(
            ['prefix' => 'privilege'],
            function () {
                // 所有权限列表
                Route::get('list', 'PrivilegeController@getPrivilegeList');
                // 添加权限
                Route::post(
                    'add',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['root'],
                        'uses' => 'PrivilegeController@addPrivilege',
                    ]
                );
                // 修改权限状态
                Route::post(
                    'status/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['root'],
                        'uses' => 'PrivilegeController@updatePrivilegeStatus',
                    ]
                );
                // 修改权限
                Route::post(
                    'update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['root'],
                        'uses' => 'PrivilegeController@updatePrivilege',
                    ]
                );
                // 权限信息获取
                Route::get(
                    'info',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['root'],
                        'uses' => 'PrivilegeController@getPrivilegeInfo',
                    ]
                );
            }
        );

        //=======================
        //  商贸公司管理类
        //=======================
        Route::group(
            ['prefix' => 'trade'],
            function () {
                // 商贸公司列表
                Route::get(
                    'list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'trade_list',
                            'trade_companys',
                            'trade',
                        ],
                        'uses' => 'TradeController@getTradeList',
                    ]
                );
                // 商贸公司添加
                Route::post(
                    'add',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['trade_add'],
                        'uses' => 'TradeController@addTrade',
                    ]
                );
                // 商贸公司详细信息获取
                Route::get(
                    'info',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'trade_info_update',
                            'trade_info',
                            'trade_companys',
                            'trade',
                        ],
                        'uses' => 'TradeController@getTradeInfo',
                    ]
                );
                // 商贸公司详细信息修改
                Route::post(
                    'info/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['trade_info_update'],
                        'uses' => 'TradeController@updateTradeInfo',
                    ]
                );
                // 商贸公司状态修改
                Route::post(
                    'status/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'trade_status_update',
                            'trade_status_update_delete',
                        ],
                        'uses' => 'TradeController@updateTradeStatus',
                    ]
                );
                Route::get(
                    'fees/list',
                    'TradeController@getFeesTypeList'
                );
            }
        );

        //=======================
        //  每日推送管理
        //=======================
        Route::group(
            ['prefix' => 'operate'],
            function () {
                // 可接收今日推文的用户信息
                Route::get(
                    'daily-news/receive-user/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'operate_daily_news_receive_user_list',
                            'operate_daily_news',
                        ],
                        'uses' => 'DailyNewsController@getDailyNewsReceiveUserList',
                    ]
                );
                // 今日推文查询
                Route::get(
                    'today-article/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'operate_today_article_list',
                            'operate_daily_news',
                        ],
                        'uses' => 'DailyNewsController@getTodayArticleList',
                    ]
                );
                // 今日推文编辑（添加/修改）
                Route::post(
                    'today-article/edit',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_today_article_edit'],
                        'uses' => 'DailyNewsController@editTodayArticle',
                    ]
                );
                // 今日推文删除
                Route::post(
                    'today-article/delete',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_today_article_delete'],
                        'uses' => 'DailyNewsController@delTodayArticle',
                    ]
                );
                // 每日推送日志查询
                Route::get(
                    'daily-news/log/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'operate_daily_news_log_list',
                            'operate_daily_news',
                        ],
                        'uses' => 'DailyNewsController@getDailyNewsSendLog',
                    ]
                );
                // 今日推送商品查询
                Route::get(
                    'daily-news/goods/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'operate_today_article_goods_list',
                            'operate_daily_news',
                        ],
                        'uses' => 'DailyNewsController@getTodaySendGoodsList',
                    ]
                );
                // 今日推送商品屏蔽操作
                Route::post(
                    'daily-news/goods/shield',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_today_article_goods_shield'],
                        'uses' => 'DailyNewsController@shieldTodaySendGoods',
                    ]
                );
                // 获取每日推文管理员信息
                Route::get(
                    'daily-news/manage/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'operate_daily_news_manage_list',
                            'operate_daily_news',
                        ],
                        'uses' => 'DailyNewsController@getNewsManageInfo',
                    ]
                );
                // 编辑每日推文管理员信息(添加、修改) operate_daily_news_manag
                Route::post(
                    'daily-news/manage/edit',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_daily_news_manage_edit'],
                        'uses' => 'DailyNewsController@editNewsManageInfo',
                    ]
                );
                // 添加推荐榜商品
                Route::post(
                    'daily-news/goods/recommend',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_daily_news_recommend_goods_add'],
                        'uses' => 'DailyNewsController@addRecommendGoods',
                    ]
                );
                // 删除单个推荐榜商品
                Route::post(
                    'daily-news/recommend/goods/remove',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_daily_news_recommend_goods_del'],
                        'uses' => 'DailyNewsController@delRecommendGoods',
                    ]
                );
                // 删除所有推荐榜商品
                Route::post(
                    'daily-news/recommend/goods/remove-all',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_daily_news_recommend_goods_del_all'],
                        'uses' => 'DailyNewsController@delRecommendGoodsAll',
                    ]
                );
                // 置顶当前推荐榜商品
                Route::post(
                    'daily-news/recommend/goods/sort',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['operate_daily_news_recommend_goods_sort'],
                        'uses' => 'DailyNewsController@sortRecommendGoods',
                    ]
                );
            }
        );

        //=======================
        //  店铺管理类
        //=======================
        Route::group(
            ['prefix' => 'shop'],
            function () {
                // 店铺类型列表
                Route::get('type/list', 'ShopController@getShopTypeList');
                // 店铺类型信息（和店铺类型列表一样，只是键名变更了）
                Route::get('type/info', 'ShopController@getShopTypeInfo');
                // 所在片区的发货市场获取
                Route::get('custom-area/shipment-market/list', 'ShopController@getAreaShipmentMarketList');
                // 有待审核商品的市场及店铺列表
                Route::get('new-goods/market/list', 'ShopController@getNewGoodsMarketList');
            }
        );

        //=======================
        //  其它公共数据类
        //=======================
        Route::group(
            ['prefix' => 'other'],
            function () {
                // 国家行政区域列表
                Route::get('area/list', 'OtherController@getAreaList');
                // 每日推文类型获取
                Route::get('today-article/type', 'OtherController@getDailyNewsTypeList');
                // 自定义区域数据(大区)
                Route::get('custom-area/list', 'OtherController@getCustomAreaList');
                // OSS前端数据签名
                Route::get('oss/signature', 'OtherController@getOssDataSignature');
                // OSS前端签名需要的数据(OSS服务器登录ID)
                Route::get('oss/identity/data', 'OtherController@getOssIdentityData');
                // 商品属性可输入格式获取
                Route::get('goods/type/attr/input-format/list', 'OtherController@getGoodsInputFormatList');
                // 商品计量单位获取
                Route::get('goods/units', 'OtherController@getGoodsUnits');
                // 商品国别获取
                Route::get('goods/smuggles', 'OtherController@getGoodsSmuggles');
            }
        );

        //=======================
        //  商品管理类
        //=======================
        Route::group(
            ['prefix' => 'goods'],
            function () {
                // 获取普通商品列表
                Route::get(
                    '/ordinary/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_query_list'],
                        'uses' => 'GoodsListController@getOrdinaryGoodsList',
                    ]
                );
                // 删除(普通)商品
                Route::post(
                    '/ordinary/delete',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_delete'],
                        'uses' => 'GoodsOperationController@deleteOrdinaryGoods',
                    ]
                );
                // 刷新(普通)商品价格
                Route::post(
                    '/ordinary/price-refresh',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_price_refresh'],
                        'uses' => 'GoodsOperationController@refreshOrdinaryGoodsPrice',
                    ]
                );
                // 下架(普通)商品
                Route::post(
                    '/ordinary/sold-out',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_sold_out'],
                        'uses' => 'GoodsOperationController@soldOutOrdinaryGoods',
                    ]
                );
                // 上架(普通)商品
                Route::post(
                    '/ordinary/on-sale',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_on_sale'],
                        'uses' => 'GoodsOperationController@onSaleOrdinaryGoods',
                    ]
                );
                // 恢复删除的(普通)商品
                Route::post(
                    '/ordinary/undelete',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_restore_delete'],
                        'uses' => 'GoodsOperationController@unDeleteOrdinaryGoods',
                    ]
                );
                // 获取商品操作日志
                Route::get(
                    '/logs/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_logs_list',
                            'goods_query_list',
                        ],
                        'uses' => 'GoodsListController@getGoodsOperationLogs',
                    ]
                );
                // 商品分类添加
                Route::post(
                    'type/add',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_type_add'],
                        'uses' => 'GoodsTypeController@addGoodsType',
                    ]
                );
                // 商品分类列表
                Route::get(
                    'type/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_type_list', 'goods_type'],
                        'uses' => 'GoodsTypeController@getGoodsTypeList',
                    ]
                );
                // 当前商品分类详情
                Route::get(
                    'type/info',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_type_update',
                            'goods_type_list',
                        ],
                        'uses' => 'GoodsTypeController@getGoodsTypeInfo',
                    ]
                );
                // 商品分类修改
                Route::post(
                    'type/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_type_update'],
                        'uses' => 'GoodsTypeController@updateGoodsType',
                    ]
                );
                // 商品分类删除
                Route::post(
                    'type/delete',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_type_del'],
                        'uses' => 'GoodsTypeController@delGoodsType',
                    ]
                );
                // 商品分类排序
                Route::post(
                    'type/sort',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_type_sort'],
                        'uses' => 'GoodsTypeController@sortGoodsType',
                    ]
                );
                // 获取商品分类基本属性
                Route::get(
                    'type/basic-attr/get',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_type_basic-attr_list',
                            'goods_type_list',
                        ],
                        'uses' => 'GoodsTypeConstrainsController@getGoodsBasicAttr',
                    ]
                );
                // 添加修改商品分类基本属性
                Route::post(
                    'type/basic-attr/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_type_basic-attr_update',
                            'goods_type_update',
                        ],
                        'uses' => 'GoodsTypeConstrainsController@updateGoodsBasicAttr',
                    ]
                );
                // 商品品牌列表
                Route::get(
                    'brands/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_brands_list', 'goods_brands'],
                        'uses' => 'BrandsController@getBrandsList',
                    ]
                );
                // 创建商品品牌
                Route::post(
                    'brands/add',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_brands_add'],
                        'uses' => 'BrandsController@createBrand',
                    ]
                );
                // 更新商品品牌
                Route::post(
                    'brands/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_brands_update'],
                        'uses' => 'BrandsController@updateBrand',
                    ]
                );
                // 删除商品品牌
                Route::post(
                    'brands/delete',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_brands_del'],
                        'uses' => 'BrandsController@deleteBrand',
                    ]
                );
                // 商品分类特殊属性添加/修改
                Route::post(
                    'type/special-attr/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_type_special-attr_update',
                            'goods_type_update',
                        ],
                        'uses' => 'GoodsTypeSpecialAttrController@updateGoodsTypeSpecialAttr',
                    ]
                );
                // 商品分类特殊属性信息列表
                Route::get(
                    'type/special-attr/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_type_special-attr_list',
                            'goods_type_list',
                        ],
                        'uses' => 'GoodsTypeSpecialAttrController@getGoodsTypeSpecialAttrList',
                    ]
                );
                // 商品分类特殊属性删除
                Route::post(
                    'type/special-attr/delete',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_type_special-attr_del',
                            'goods_type_del',
                        ],
                        'uses' => 'GoodsTypeSpecialAttrController@delGoodsTypeSpecialAttr',
                    ]
                );
                // 商品转移（添加）
                Route::post(
                    'add',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_add', 'goods_transfer_list'],
                        'uses' => 'GoodsController@addGoods',
                    ]
                );
                // 商品图片删除
                Route::post(
                    'picture/del',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_picture_del',
                            'goods_add',
                            'goods_transfer_list',
                        ],
                        'uses' => 'GoodsOperationController@delGoodsPicture',
                    ]
                );
                // 旧商品图片(包括检验报告)的获取
                Route::get('picture/list', 'GoodsController@getOldGoodsPicture');
                // 检验报告图片删除
                Route::post(
                    'inspection-report/picture/del',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_inspection-report_picture_del',
                            'goods_add',
                            'goods_transfer_list',
                        ],
                        'uses' => 'GoodsOperationController@delGoodsInspectionReport',
                    ]
                );
                // 供应商信息查询
                Route::get(
                    'shop/info',
                    [
                        'uses' => 'GoodsController@getShopInfo',
                    ]
                );
                // 待审核商品列表
                Route::get(
                    'new-goods/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_audit_list'],
                        'uses' => 'GoodsListController@getNewGoodsList',
                    ]
                );
                // 审核通过
                Route::post(
                    'audit/pass',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_audit_pass'],
                        'uses' => 'GoodsOperationController@auditPass',
                    ]
                );
                // 审核拒绝
                Route::post(
                    'audit/refused',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_audit_refused'],
                        'uses' => 'GoodsOperationController@auditRefused',
                    ]
                );
                // 商品历史价格日志列表
                Route::get(
                    'history-prices/list',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_history_prices_list'],
                        'uses' => 'GoodsListController@getHistoryPricesList',
                    ]
                );
                // 商品详情信息
                Route::get(
                    'info',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_info',
                            'goods_update',
                            'goods_query_list',
                            'goods_audit_list',
                        ],
                        'uses' => 'GoodsController@getGoodsInfo',
                    ]
                );

                Route::post(
                    'same',
                    [
                        'middleware' => 'permissions',
                        'permissions' => [
                            'goods_info',
                            'goods_update',
                            'goods_query_list',
                            'goods_audit_list',
                        ],
                        'uses' => 'GoodsController@getSameGoods',
                    ]
                );

                // 商品信息更新
                Route::post(
                    'update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['goods_update'],
                        'uses' => 'GoodsController@updateGoodsInfo',
                    ]
                );

                Route::get(
                    'price-rules',
                    'Goods\GoodsPriceRuleController@getPriceRule'
                );

                // ================
                // 商品转移（临时）
                // ================
                // 有商品可转移的店铺列表
                Route::get('transfer/shop/list', 'GoodsTransferController@getShopList');
                // 待转移商品列表
                Route::get('transfer/list', 'GoodsTransferController@getGoodsList');
                // 屏蔽旧商品的转移
                Route::post('transfer/shielding', 'GoodsTransferController@shieldingOldGoodsTransfer');
                // 删除待转移的旧商品
                Route::post('transfer/old-goods/del', 'GoodsTransferController@delOldGoods');
            }
        );

        //=======================
        //  活动管理类
        //=======================
        Route::group(
            ['prefix' => 'activities'],
            function () {
                // 获取活动类型
                Route::get('type', 'ActivityController@getActivitiesTypes');
                // 获取活动列表
                Route::get('list', 'ActivityController@getActivitiesList');
                // 添加活动
                Route::post('add', 'ActivityController@addActivity');
                // 更新活动
                Route::post('update', 'ActivityController@updateActivity');
                // 活动商品添加
                Route::post('goods/add', 'ActivityGoodsController@addActivityGoods');
                // 活动商品列表
                Route::get('goods/list', 'ActivityGoodsController@getActivityGoodsList');
                // 活动商品删除
                Route::post('goods/del', 'ActivityGoodsController@delActivityGoods');
                // 活动商品清空
                Route::post('goods/clear', 'ActivityGoodsController@clearActivityGoods');
                // 活动商品排序
                Route::post('goods/sort', 'ActivityGoodsController@sortActivityGoods');
            }
        );

        // ======================
        // 系统管理类
        // ======================
        Route::group(
            ['prefix' => 'system', 'namespace' => 'System'],
            function () {
                Route::group(
                    ['prefix' => 'version', 'namespace' => 'Version'],
                    function () {
                        // 添加版本日志
                        Route::post(
                            'log/add',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['root', 'admin'],
                                'uses' => 'VersionManageController@addVersion',
                            ]
                        );
                        // 版本日志列表
                        Route::get(
                            'log/list',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['root', 'admin'],
                                'uses' => 'VersionManageController@getVersionList',
                            ]
                        );
                        // 修改版本日志
                        Route::post(
                            'log/update',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['root', 'admin'],
                                'uses' => 'VersionManageController@updateVersion',
                            ]
                        );
                    }
                );
            }
        );
        //=======================
        //     后台市场设置
        //=======================
        Route::group(
            [
                'middleware' => 'permissions',
                'permissions' => ['province_manager'],
                'prefix' => 'market',
                'namespace' => '\Market',
            ],
            function () {
                // 获取市场列表
                Route::get('', 'MarketController@index');
                // 市场管理
                Route::get('show', 'MarketController@show');
                // 开通省份
                Route::post('add/province', 'MarketController@openProvince');
                // 开通市场
                Route::post('add/market', 'MarketController@openMarket');
            }
        );

        // ================
        //  搜索词库管理
        // ================
        Route::group(
            ['prefix' => 'search', 'namespace' => 'Search'],
            function () {
                // 更新同义词词库
                Route::post(
                    'synonym/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['search_search_synonym_update'],
                        'uses' => 'SearchConfigController@updateSearchSynonym',
                    ]
                );
                // 更新自定义分词词典
                Route::post(
                    'dict/update',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['search_custom_dict_update'],
                        'uses' => 'SearchConfigController@updateCustomDict',
                    ]
                );
                // 获取boost配置
                Route::get(
                    'boost',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['search_sort_boost_list'],
                        'uses' => 'SearchConfigController@getGoodsSortBoost',
                    ]
                );
                // 更新boost配置
                Route::post(
                    'boost',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['search_sort_boost_update'],
                        'uses' => 'SearchConfigController@updateGoodsSortBoost',
                    ]
                );
                // 商品搜索索引初始化(注释掉中间件，现在暂时不需要这项功能)
                //Route::group(['middleware' => 'index.init'], function () {
                Route::post(
                    'index/init',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['search_index_init'],
                        'uses' => 'SearchConfigController@indexInit',
                    ]
                );
                //});
                // 商品搜索词典初始化(注释掉中间件，现在暂时不需要这项功能)
                //Route::group(['middleware' => 'dict.init'], function () {
                Route::post(
                    'dict/init',
                    [
                        'middleware' => 'permissions',
                        'permissions' => ['search_dict_init'],
                        'uses' => 'SearchConfigController@dictInit',
                    ]
                );
                //});
            }
        );

        //====================
        //     店铺审核
        //====================
        Route::group(
            [
                'prefix' => 'examine',
                'namespace' => '\Examine',
            ],
            function () {
                Route::post('pass', 'ExamineController@pass');
                Route::post('refuse', 'ExamineController@refuse');
            }
        );

        //====================
        //     评价管理
        //====================
        Route::group(
            [
                'prefix' => 'shop/handle/appraise',
                'namespace' => '\Shop',
            ],
            function () {
                Route::get('list', 'AppraiseHandleController@getList');
                //采购商和供应商评价数据统计（按照店铺）
                Route::get('shops/info', 'AppraiseHandleController@appraiseShopInfo');
                //供应商评价数据统计（按照商品）
                Route::get('goods/info', 'AppraiseHandleController@appraiseGoodsInfo');
                //获取订单和订单的评价信息列表
                Route::post('list', 'AppraiseHandleController@getList');
                //评价修改
                Route::post('update_appraise', 'AppraiseHandleController@updateAppraise');
                //根据订单获取订单的评价的详细信息
                Route::get('appraise_details', 'AppraiseHandleController@getAppraiseDetails');
                //根据订单获取订单对应的修改日志信息
                Route::get('appraise_log', 'AppraiseHandleController@getAppraiseLog');
                //根据订单软删除对应的评价信息
                Route::post('appraise_delete', 'AppraiseHandleController@deleteAppraise');
                //根据订单重置对应的评价信息
                Route::post('appraise_reset', 'AppraiseHandleController@resetAppraise');
            }
        );

        //====================
        //     订单管理
        //====================
        Route::group(
            [
                'prefix' => 'order',
                'namespace' => '\Order',
            ],
            function () {
                // 获取订单列表
                Route::get('info', 'OrderController@getList');
                // 获取订单详情
                Route::get('detail', 'OrderController@getDetail');
                // 订单确认收款处理
                Route::post('payment', 'OrderController@payment');
                // 获取财务退款订单列表
                Route::get('refundInfo', 'OrderController@getRefundList');
                // 买家取消订单
                Route::post('buyerCancel', 'OrderController@buyerCancel');
                // 获取取消订单的理由列表
                Route::get('reasonList', 'OrderController@getReasonList');
                // 卖家确认发货
                Route::post('sellerShipments', 'OrderController@sellerShipments');
                // 申请-卖家取消/买家退款/买家退货
                Route::post('saleApply', 'OrderController@saleApply');
                // 取消申请-卖家取消/买家退款/买家退货
                Route::post('cancelApply', 'OrderController@cancelApply');
                // 同意-卖家取消/买家退款/买家退货
                Route::post('agreeApply', 'OrderController@agreeApply');
                // 拒绝-卖家取消/买家退款/买家退货
                Route::post('refuseApply', 'OrderController@refuseApply');
                // 获取退款/退货/取消的金额
                Route::post('refundPrice', 'OrderController@getRefundPrice');
                // 提醒卖家发货
                Route::post('remindSend', 'OrderController@remindSend');
                // 提醒买家收货
                Route::post('remindReceive', 'OrderController@remindReceive');
                // 卖家确认退款
                Route::post('sellerRefund', 'OrderController@sellerRefund');
                // 买家发货
                Route::post('buyerSend', 'OrderController@buyerSend');
                // 买家确认收货
                Route::post('buyerDelivery', 'OrderController@buyerDelivery');
                // 再次申请-卖家取消/买家退款/买家退货
                Route::post('againApply', 'OrderController@againApply');
                // 修改退款金额
                Route::post('editRefund', 'OrderController@editRefund');
                // 财务撤回
                Route::post('financerRecall', 'OrderController@financerRecall');
            }
        );

        //====================
        //     反馈管理
        //====================
        Route::group(
            [
                'prefix' => 'tickling',
                'namespace' => '\Tickling',
            ],
            function () {
                Route::get('info', 'TicklController@GetTicking');
                Route::get('oneInfo', 'TicklController@getTickingInfo');

                Route::get('sp/info', 'TicklController@GetSpTicking');
                Route::get('sp/oneInfo', 'TicklController@getSpTickingInfo');

                Route::post('reply', 'TicklController@rePlay');
            }
        );

        //====================
        //     店铺管理
        //====================
        Route::group(
            [
                'prefix' => 'shop/handle',
                'namespace' => '\Shop',
            ],
            function () {
                Route::post('', 'ShopHandleController@index');
                Route::post('show', 'ShopHandleController@show');
                Route::post('update', 'ShopHandleController@update');
                Route::post('close', 'ShopHandleController@close');
                // 修改店铺信息的前置信息获取(省市县、一批市场)
                Route::post('preInfo1', 'ShopHandleController@getAreaOrPianqu');
                // 修改店铺信息的前置信息获取(一批根据大区id获取省市县)
                Route::post('preInfo2', 'ShopHandleController@getAreaOfPianqu');
            }
        );

        //====================
        //     店铺成员管理
        //====================
        Route::group(
            [
                'prefix' => 'shop/member',
                'namespace' => '\Shop',
            ],
            function () {
                Route::post('', 'MemberController@index');
                Route::post('del', 'MemberController@del');
                // 获得店铺可用的角色
                Route::get('role', 'MemberController@getMemberRole');
                // 添加店铺成员
                Route::post('add', 'MemberController@addMember');
                // 生成店铺成员添加的二维码
                Route::get('add_code', 'MemberController@getMemberAddCode');
            }
        );

        // ===================
        //  获取微信分组
        // ===================
        Route::group(
            [
                'prefix' => 'wechat',
                'namespace' => '\Wechat',
            ],
            function () {
                Route::get('', 'WechatController@index'); // 获取微信分组
                Route::post('set', 'WechatController@store'); // 设置店铺微信分组
                route::post('get', 'WechatController@get'); // 获取用户微信分组id
            }
        );

        // ======================
        // 通用数据部分
        // ======================
        Route::group(
            ['namespace' => 'Common'],
            function () {
                // 买家首页Banner 添加
                Route::post(
                    'banner/add',
                    [
                        //'middleware'  => 'permissions',
                        //'permissions' => ['banner_buyer_index_add'],
                        'uses' => 'BannerController@addBuyerIndexBanner',
                    ]
                );
                // 买家首页banner列表获取
                Route::get(
                    'banner/list',
                    [
                        //'middleware'  => 'permissions',
                        //'permissions' => ['banner_buyer_index_list'],
                        'uses' => 'BannerController@getBuyerIndexBanner',
                    ]
                );
                // =================
                // Banner公共部分
                // =================
                Route::group(
                    ['prefix' => 'common/banner', 'namespace' => 'Banner'],
                    function () {
                        // 获取Banner类型
                        Route::get('type/list', 'BannerController@getTypeList');
                        // 获取Banner 详情
                        Route::get(
                            'info',
                            [
                                //'middleware'  => 'permissions',
                                //'permissions' => ['banner_info'],
                                'uses' => 'BannerController@getBannerInfo',
                            ]
                        );
                        // 修改Banner信息
                        Route::post(
                            'update',
                            [
                                //'middleware'  => 'permissions',
                                //'permissions' => ['banner_update'],
                                'uses' => 'BannerController@updateBannerInfo',
                            ]
                        );
                        // banner显示顺序调整（交换排序）
                        Route::post(
                            'position/update',
                            [
                                //'middleware'  => 'permissions',
                                //'permissions' => ['banner_move'],
                                'uses' => 'BannerController@updateBannerSort',
                            ]
                        );
                        // banner上、下架(就是修改上、下架时间)
                        Route::post(
                            'show-time/update',
                            [
                                //'middleware'  => 'permissions',
                                //'permissions' => ['banner_off_on'],
                                'uses' => 'BannerController@updateShowTime',
                            ]
                        );
                    }
                );
            }
        );

        // ============================
        // 商城广告及推荐商品等管理
        // ============================
        Route::group(
            ['prefix' => 'operation-manage', 'namespace' => 'OperationManage'],
            function () {
                // 获取Banner类型
                Route::get('type/list', 'BannerController@getTypeList');
                // 获取Banner 详情
                Route::get(
                    'info',
                    [
                        //'middleware'  => 'permissions',
                        //'permissions' => ['banner_info'],
                        'uses' => 'BannerController@getBannerInfo',
                    ]
                );
                // 修改Banner信息
                Route::post(
                    'update',
                    [
                        //'middleware'  => 'permissions',
                        //'permissions' => ['banner_update'],
                        'uses' => 'BannerController@updateBannerInfo',
                    ]
                );
                // banner显示顺序调整（交换排序）
                Route::post(
                    'position/update',
                    [
                        //'middleware'  => 'permissions',
                        //'permissions' => ['banner_move'],
                        'uses' => 'BannerController@updateBannerSort',
                    ]
                );
                // banner上、下架(就是修改上、下架时间)
                Route::post(
                    'show-time/update',
                    [
                        //'middleware'  => 'permissions',
                        //'permissions' => ['banner_off_on'],
                        'uses' => 'BannerController@updateShowTime',
                    ]
                );
                // 首页
                Route::group(
                    ['prefix' => 'index-manage', 'namespace' => 'IndexManage'],
                    function () {
                        // 添加推荐商品
                        Route::post(
                            'recommend-goods/add',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['recommend-goods_add'],
                                'uses' => 'RecommendGoodsController@addGoods',
                            ]
                        );
                        // 推荐商品列表
                        Route::get(
                            'recommend-goods/list',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['recommend-goods_list'],
                                'uses' => 'RecommendGoodsController@getGoodsList',
                            ]
                        );
                        // 下架推荐商品
                        Route::post(
                            'recommend-goods/pull-off',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['recommend-goods_off'],
                                'uses' => 'RecommendGoodsController@pullOffGoods',
                            ]
                        );
                        //移动推荐商品
                        Route::post(
                            'recommend-goods/move',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['recommend-goods_move'],
                                'uses' => 'RecommendGoodsController@moveGoods',
                            ]
                        );
                        //添加优质供应商
                        Route::post(
                            'high-quality-suppliers/add',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['high-quality-suppliers_add'],
                                'uses' => 'HighQualitySupplierController@addSupplier',
                            ]
                        );
                        //优质供应商列表
                        Route::get(
                            'high-quality-suppliers/list',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['high-quality-suppliers_list'],
                                'uses' => 'HighQualitySupplierController@getSuppliersList',
                            ]
                        );
                        //下架优质供应商
                        Route::post(
                            'high-quality-suppliers/pull-off',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['high-quality-suppliers_off'],
                                'uses' => 'HighQualitySupplierController@pullOffSupplier',
                            ]
                        );
                        //移动优质供应商
                        Route::post(
                            'high-quality-suppliers/move',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['high-quality-suppliers_move'],
                                'uses' => 'HighQualitySupplierController@moveSupplier',
                            ]
                        );
                        //添加新上好货
                        Route::post(
                            'new-goods/add',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['new-goods_add'],
                                'uses' => 'NewGoodsController@addNewGoods',
                            ]
                        );
                        //新上好货列表
                        Route::get(
                            'new-goods/list',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['new-goods_list'],
                                'uses' => 'NewGoodsController@getNewGoodsList',
                            ]
                        );
                        //下架新上好货
                        Route::post(
                            'new-goods/pull-off',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['new-goods_off'],
                                'uses' => 'NewGoodsController@pullOffGoods',
                            ]
                        );
                        //移动新上好货
                        Route::post(
                            'new-goods/move',
                            [
                                'middleware' => 'permissions',
                                'permissions' => ['new-goods_move'],
                                'uses' => 'NewGoodsController@moveGoods',
                            ]
                        );
                        //添加品牌到品牌馆
                        Route::post('brands-house/add', 'BrandsHouseController@addBrands');
                        //品牌馆列表
                        Route::get('brands-house/list', 'BrandsHouseController@getBrandsList');
                        //下架品牌
                        Route::post('brands-house/pull-off', 'BrandsHouseController@pullOffBrands');
                        //添加弹窗广告
                        Route::post('popup-ads/add', 'PopupAdsController@addAds');
                        //弹窗广告列表
                        Route::get('popup-ads/list', 'PopupAdsController@getAdsList');
                        //下架弹窗广告
                        Route::post('popup-ads/pull-off', 'PopupAdsController@pullOffAds');
                        //通过brandId获取品牌
                        Route::get('brand-name', 'CommonController@getBrandById');
                        //通过shopId获取店铺名
                        Route::get('shop-name', 'CommonController@getSupplierById');
                    }
                );
                // 商城首页Banner
                Route::group(
                    ['prefix' => 'buyer-index', 'namespace' => 'Banner'],
                    function () {
                        // Banner 添加
                        Route::post(
                            'banner/add',
                            [
                                //'middleware'  => 'permissions',
                                //'permissions' => ['banner_buyer_index_add'],
                                'uses' => 'BannerController@addBuyerIndexBanner',
                            ]
                        );
                        // banner 列表获取
                        Route::get(
                            'banner/list',
                            [
                                //'middleware'  => 'permissions',
                                //'permissions' => ['banner_buyer_index_list'],
                                'uses' => 'BannerController@getBuyerIndexBanner',
                            ]
                        );
                    }
                );
            }
        );
    }
);

// 获取省市信息
Route::get('city', 'AreaController@getCity');
Route::get('county', 'AreaController@getCounty');
// 获取已开通省信息
Route::get('province', 'AreaController@getOpenProvince');
