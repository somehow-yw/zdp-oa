<?php

namespace App\Http\Controllers\ProviderService;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Services\ProviderService\ProviderService;
use Illuminate\Http\Request;
use Validator;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;
use Zdp\ServiceProvider\Data\Models\WechatAccount;
use Zdp\ServiceProvider\Data\Utils\UserMenu;
use Zdp\ServiceProvider\Data\Utils\UserTag;

class ProviderController extends Controller
{
    private $providerService;

    public function __construct(ProviderService $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * 获取所有服务商申请列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->validate(
            $request,
            [
                'page'   => 'integer|min:1',
                'size'   => 'integer|min:10|max:50',
                'status' => 'integer|in:' . ServiceProvider::ENDING . ',' .
                            ServiceProvider::PASS . ',' . ServiceProvider::DENY,
            ], [
                'page.integer'   => '页数为整数',
                'page.min'       => '页数必须大于等于1',
                'size.integer'   => '页面数据条数必须为整数',
                'size.min'       => '页面数据条数必须大于等于10',
                'size.max'       => '页面数据条数不得超过50',
                'status.integer' => '状态为整数',
                'status.in'      => '状态非法',
            ]
        );
        $allApplies = $this->providerService->index(
            $request->input('page', 1),
            $request->input('size', 20),
            $request->input('status')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $allApplies,
        ]);
    }

    /**
     * 查看服务商
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $this->validate(
            $request,
            [
                'shop_id' => 'required|
                exists:mysql_service_provider.service_providers,zdp_user_id',
            ], [
                'shop_id.required' => '服务商id必须有',
                'shop_id.exists'   => '服务商不存在',
            ]
        );

        $detail = $this->providerService->show($request->input('shop_id'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $detail,
        ]);
    }

    /**
     * 服务商订单导出
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $this->validate(
            $request,
            [
                'begin'  => 'required|date',
                'end'    => 'required|date',
                'sp_id'  => 'integer|exists:mysql_service_provider.service_providers,zdp_user_id',
                'status' => 'array',
            ]
        );
        $this->providerService->export(
            $request->input('begin'),
            $request->input('end'),
            $request->input('sp_id'),
            $request->input('status', [])
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 服务商搜索
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $this->validate(
            $request,
            [
                'search_type' => 'required|in:1,2',
                'content'     => 'required',
            ]
        );
        $services = $this->providerService->search(
            $request->input('search_type'),
            $request->input('content')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $services,
        ]);
    }

    /**
     * 获取某个电话搜搜出来的店铺名、联系人、手机号(全)
     */
    public function hint(Request $request)
    {
        $services = $this->providerService->hint($request->input('mobile'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $services,
        ]);
    }

    /**
     * 服务商确认/关闭
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate(
            $request,
            [
                'sp_ids' => 'required|array',
                'handle' => 'required|integer|in:' . ServiceProvider::PASS .
                            ',' . ServiceProvider::DENY,
            ]
        );

        $userId = $request->user()->id;

        $this->providerService->handle(
            $userId,
            $request->input('handle'),
            $request->input('sp_ids')
        );

        return response()->json([
            'code'    => 0,
            'message' => '操作成功',
            'data'    => [],
        ]);
    }

    /**
     * 服务商删除
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request)
    {
        $this->validate(
            $request,
            [
                'sp_id' => 'required|integer|min:1|
                exists:mysql_service_provider.service_providers,zdp_user_id,deleted_at,NULL',
            ],
            [
                'sp_id.required' => '用户ID必须传入',
                'sp_id.integer'  => '用户ID必须是整数',
                'sp_id.min'      => '用户ID不可小于:min',
                'sp_id.exists'   => '用户不存在',
            ]
        );

        $userId = $request->user()->id;

        $this->providerService->del($userId, $request->input('sp_id', 0));

        return response()->json([
            'code'    => 0,
            'message' => '操作成功',
            'data'    => [],
        ]);
    }

    /**
     * 获取服务商操作日志
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function log(Request $request)
    {
        $this->validate(
            $request,
            [
                'page' => 'integer|min:1',
                'size' => 'integer|min:10|max:50',
            ]
        );

        $logs = $this->providerService->log(
            $request->input('page', 1),
            $request->input('size', 10)
        );
        $data = [
            'total'     => $logs->total(),
            'current'   => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'logs'      => $logs->items(),
        ];

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 服务商客户分类列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sort()
    {
        $sorts = $this->providerService->sort();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $sorts,
        ]);
    }

    /**
     * 添加服务商客户分类
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortAdd(Request $request)
    {
        $this->validate(
            $request,
            [
                'sort_name' => 'required|string|min:0|max:20|
                                unique:mysql_service_provider.shop_type,type_name',
            ],
            [
                'sort_name.required' => '分类名必须有',
                'sort_name.max'      => '分类名长度超过限制',
                'sort_name.unique'   => '分类名已存在',
            ]
        );

        $this->providerService->sortAdd($request->input('sort_name'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 更新服务商微信配置
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function updateWeChatConfig(Request $request)
    {
        $this->validate(
            $request,
            [
                'sp_id'       => 'required|exists:mysql_service_provider.service_providers,zdp_user_id',
                'appid'       => 'required|string',
                'secret'      => 'required|string',
                'token'       => 'required|string',
                'aes_key'     => 'required|string',
                'wechat_name' => 'required|string',
                'source'      => 'required|string',
                'merchant_id' => 'string',
            ],
            [
                'sp_id.required' => '服务商id不能为空',
                'sp_id.exists'   => '服务商id不存在',

                'appid.required' => 'appid不能为空',
                'appid.string'   => 'appid应该是字符串',

                'secret.required' => 'secret不能为空',
                'secret.string'   => 'secret应该是字符串',

                'token.required' => 'token不能为空',
                'token.string'   => 'token应该是字符串',

                'aes_key.required' => 'aes_key不能为空',
                'aes_key.string'   => 'aes_key应该是字符串',

                'merchant_id.string' => 'merchant_id应该是字符串',

                'wechat_name.required' => 'wechat_name不能为空',
                'wechat_name.string'   => 'wechat_name应该是字符串',

                'source.required' => '服务商二级域名不能为空',
                'source.string'   => '服务商二级域名应该是字符串',
            ]
        );

        $spId = $request->input('sp_id');

        /** @var ServiceProvider $serviceProvider */
        $serviceProvider = ServiceProvider::find($spId);

        //        if ($serviceProvider->status != ServiceProvider::PASS) {
        //            throw  new  AppException("该服务商暂未审核通过");
        //        }

        /** @var WechatAccount $weChatAccount */
        $weChatAccount = $serviceProvider->wechatAccount;

        if (!empty($weChatAccount->appid) &&
            !empty($weChatAccount->secret) &&
            !empty($weChatAccount->token) &&
            !empty($weChatAccount->aes_key) &&
            !empty($weChatAccount->merchant_id)
        ) {
            throw new AppException("您的微信账号相关配置已经完善");
        }
        \DB::connection('mysql_service_provider')->transaction(function () use (
            $weChatAccount,
            $request
        ) {
            $weChatAccount->update(
                [
                    'appid'       => $request->input('appid'),
                    'secret'      => $request->input('secret'),
                    'token'       => $request->input('token'),
                    'aes_key'     => $request->input('aes_key'),
                    'wechat_name' => $request->input('wechat_name'),
                    'source'      => $request->input('source'),
                    'merchant_id' => $request->input('merchant_id', ''),
                ]
            );
        });

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 初始化微信标签与菜单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function initWechatTagAndMenu(Request $request)
    {
        $this->validate(
            $request,
            [
                'source' => 'required|string|between:1,32',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',
            ]
        );

        $source = $request->input('source');
        // 初始tag和menu
        $config = WechatAccount::getWeChatConfigBySource($source);
        $config = array_merge(config('wechat'), $config);
        $userTag = new UserTag($config);
        $userTag->createTag();
        $userTag->initWxMenu($source);
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    // ===================
    // 服务商微信标签管理
    // ===================
    /**
     * 获得服务商微信标签信息
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWechatTags(Request $request)
    {
        $this->validate(
            $request,
            [
                'source' => 'required|string|between:1,32',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',
            ]
        );
        $wechatTagObj = new UserMenu($request->input('source'));
        $wechatReInfoArr = $wechatTagObj->getUserTag();
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $wechatReInfoArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 更改服务商微信标签名称
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateWechatTag(Request $request)
    {
        $this->validate(
            $request,
            [
                'source'   => 'required|string|between:1,32',
                'tag_id'   => 'required|integer|min:1',
                'tag_name' => 'required|string|between:1,20',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',

                'tag_id.required' => '标签ID必须有',
                'tag_id.string'   => '标签ID必须是一个整型',
                'tag_id.min'      => '标签ID不可小于:min',

                'tag_name.required' => '标签名称必须有',
                'tag_name.string'   => '标签名称须是一个字符串',
                'tag_name.between'  => '标签名称长度必须在:min到:max位',
            ]
        );
        $wechatTagObj = new UserMenu($request->input('source'));
        $wechatReInfoArr = $wechatTagObj->updateUserTag(
            $request->input('tag_id'),
            $request->input('tag_name')
        );
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $wechatReInfoArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 删除服务商微信标签
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delWechatTag(Request $request)
    {
        $this->validate(
            $request,
            [
                'source' => 'required|string|between:1,32',
                'tag_id' => 'required|integer|min:1',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',

                'tag_id.required' => '标签ID必须有',
                'tag_id.string'   => '标签ID必须是一个整型',
                'tag_id.min'      => '标签ID不可小于:min',
            ]
        );
        $wechatTagObj = new UserMenu($request->input('source'));
        $wechatReInfoArr = $wechatTagObj->delUserTag($request->input('tag_id'));
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $wechatReInfoArr,
        ];

        return response()->json($reDataArr);
    }

    // ===================
    // 服务商微信菜单管理
    // ===================
    /**
     * 获取服务商微信菜单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWechatMenus(Request $request)
    {
        $this->validate(
            $request,
            [
                'source' => 'required|string|between:1,32',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',
            ]
        );
        $wechatMenuObj = new UserMenu($request->input('source'));
        $wechatReInfoArr = $wechatMenuObj->getMenu();
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $wechatReInfoArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 编辑服务商微信菜单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     */
    public function editWechatMenu(Request $request)
    {
        $requestArr = $request->all();
        /** @var \Illuminate\Contracts\Validation\Validator $validator */
        $validator = Validator::make(
            $requestArr,
            [
                'source'     => 'required|string|between:1,32',
                'buttons'    => 'required|array|min:1',
                'match_rule' => 'array',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',

                'buttons.required' => '菜单内容必须有',
                'buttons.array'    => '菜单内容必须是一个数组格式',
                'buttons.min'      => '菜单个数不可小于:min',

                'match_rule.required' => '个性菜单设置项必须有',
                'match_rule.array'    => '个性菜单设置项必须是一个数组格式',
            ]
        );
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->first();
            throw new AppException($errorMsg);
        }

        $wechatMenuObj = new UserMenu($requestArr['source']);
        $matchRule =
            empty($requestArr['match_rule']) ? [] : $requestArr['match_rule'];
        $wechatReInfoArr =
            $wechatMenuObj->editMenu($requestArr['buttons'], $matchRule);
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $wechatReInfoArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 删除服务商微信菜单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delWechatMenu(Request $request)
    {
        $this->validate(
            $request,
            [
                'source'  => 'required|string|between:1,32',
                'menu_id' => 'string|min:1',
            ],
            [
                'source.required' => '服务商标识必须有',
                'source.string'   => '服务商标识必须是一个字符串',
                'source.between'  => '服务商标识长度必须在:min到:max位',

                'menu_id.required' => '菜单ID必须有',
                'menu_id.string'   => '菜单ID必须是一个字符串',
                'menu_id.min'      => '菜单ID长度不可小于:min',
            ]
        );
        $wechatMenuObj = new UserMenu($request->input('source'));
        $wechatReInfoArr =
            $wechatMenuObj->delMenu($request->input('menu_id', 0));
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $wechatReInfoArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 获取微信菜单类型.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWechatMenuTypes()
    {
        $wechatMenuObj = new UserMenu('');
        $reInfoArr = $wechatMenuObj->getMenuType();
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reInfoArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 服务商信息更改
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     */
    public function updateSpInfo(Request $request, ProviderService $service)
    {
        $this->validate(
            $request,
            [
                'uid'     => 'required|integer|min:1',
                'updates' => 'required',
            ],
            [
                'uid.required' => '服务商ID必须有',
                'uid.integer'  => '服务商ID必须是一个整型',
                'uid.min'      => '服务商ID不可小于:min',

                'updates.required' => '更改信息必须有',
            ]
        );

        $requestArr = $request->all();
        $updateArr =
            empty($requestArr['updates']) ? [] : $requestArr['updates'];
        if (!is_array($updateArr)) {
            throw new AppException('更改信息格式不正确');
        }

        $updateNum = $service->updateInfo($requestArr['uid'], $updateArr);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => ['update_num' => $updateNum],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 获取中国所有省
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvinces()
    {
        $provinces = $this->providerService->getProvince();

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $provinces,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 获取某区域下的所有子区域
     *
     * @param $id integer
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getChildren($id)
    {
        if (empty($id)) {
            throw new \Exception('请传入需要查询的区域id');
        }

        $cities = $this->providerService->getChildren($id);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $cities,
        ];

        return response()->json($reDataArr);
    }

}
