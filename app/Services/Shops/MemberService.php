<?php

namespace App\Services\Shops;

use App\Exceptions\AppException;
use App\Jobs\MemberWeChatGroupAdjust;
use App\Repositories\Shops\Contracts\ShopRepository;
use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;
use Zdp\Main\Data\Models\DpCartInfo;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\Main\Data\Models\DpOpderForm;
use Zdp\Main\Data\Models\DpShangHuInfo;
use App\Exceptions\User\ShopException;
use Zdp\Main\Data\Models\DpShopCart;
use Zdp\Main\Data\Models\DpShopInfo;

class MemberService
{
    protected $shopRepo;
    protected $httpRequest;

    public function __construct(ShopRepository $shopRepo, HTTPRequestUtil $httpRequest)
    {
        $this->shopRepo = $shopRepo;
        $this->httpRequest = $httpRequest;
    }

    /**
     * 后台删除店铺成员
     *
     * @param $shopId
     * @param $userId
     *
     * @return int
     * @throws \Exception
     */
    public function del($shopId, $userId)
    {
        // 取得将被删除的成员信息
        $userInfo = DpShangHuInfo::where('shId', $userId)
            ->whereIn('shengheAct', [0, 1])
            ->select(['lianxiTel', 'unionId'])
            ->first();
        if (empty($userInfo->lianxiTel)) {
            throw new AppException('没有该成员信息');
        }
        $userSave = [
            'shengheAct' => 3,
            'lianxiTel'  => $userInfo->lianxiTel . '--已删除--' . $userId,
            'unionId'    => $userInfo->unionId . '--已删除--' . $userId,
        ];
        // 查询大老板id
        $shInfo = DpShangHuInfo::where('shopId', $shopId)
            ->where('shId', $userId)
            ->where('laoBanHao', '<>', 0)
            ->select(['laoBanId', 'laoBanHao'])
            ->first();
        if ($shInfo->laoBanHao == 0 || $shInfo->laoBanId == 0) {
            throw new \Exception('不能直接删除大老板');
        }
        // 查询该员工添加的所有商品
        $goodsIds = DpGoodsInfo::select('id')->where('shid', $userId)->get();
        \DB::connection('mysql_zdp_main')->transaction(function () use (
            $goodsIds,
            $shInfo,
            $userId,
            $userSave
        ) {
            if (!empty($goodsIds)) {
                foreach ($goodsIds as $goodsId) {
                    // 将要删除的成员商品转移到大老板下
                    DpGoodsInfo::where('id', $goodsId)
                        ->update(['shid' => $shInfo->laoBanId]);
                }
            }
            // 删除该成员
            DpShangHuInfo::where('shId', $userId)
                ->whereIn('shengheAct', [0, 1])
                ->update($userSave);
        });
    }

    /**
     * 获取店铺所有角色
     *
     * @param $isBoos integer 是否保留BOOS角色 0=不保留 1=保留
     *
     * @return array
     */
    public function getMemberRole($isBoos)
    {
        // 取得角色信息
        $roleArr = collect(config('shop.role'))->keyBy('id')->all();
        if (empty($isBoos)) {
            unset($roleArr[0]);
        }

        return array_merge($roleArr, []);
    }

    /**
     * 添加绑定店铺成员
     *
     * @param $shopId integer 绑定者店铺ID
     * @param $mobile string 被绑定者注册电话
     * @param $role   integer 被绑定者的角色编号
     *
     * @throws \App\Exceptions\User\ShopException
     */
    public function addMember($shopId, $mobile, $role)
    {
        // 根据手机号取出被绑定者的信息
        $selectArr = [
            'member' => [
                'shId',
                'shopId',
                'OpenID',
                'unionId',
                'laoBanId',
                'laoBanHao',
                'lianxiTel',
                'unionName',
                'unionPic',
                'xingming',
                'shengheAct',
                'integralall',
                'guanzhuAct',
                'wegroupid',
            ],
            'shop'   => ['shopId', 'laoBanId', 'dianPuName', 'trenchnum', 'goodsNum'],
        ];
        $memberInfo = $this->shopRepo->getMemberInfoByMobile($mobile, $selectArr);
        $boosNum = 0;
        if (is_null($memberInfo)) {
            // 成员未注册
            throw new ShopException(ShopException::MEMBER_MOBILE_NOT);
        } elseif (empty($memberInfo->guanzhuAct)) {
            // 已取消关注此公众号
            throw new ShopException(ShopException::MEMBER_UNSUBSCRIBE);
        } elseif (empty($memberInfo->laoBanHao)) {
            // 成员是大老板
            // 取得其店铺下的成员数
            $memberNum = DpShangHuInfo::query()
                ->where('shengheAct', DpShangHuInfo::STATUS_PASS)
                ->where('shopId', $memberInfo->shopId)
                ->where('laoBanHao', '>', 0)
                ->count();
            if ($memberNum) {
                // 如果有成员
                throw new ShopException(ShopException::BAND_SHOP_EXIST_MEMBER);
            }
        }
        // 供应商店铺类型
        $sellShopTypeArr = [
            DpShopInfo::YIPI,
            DpShopInfo::VENDOR,
        ];
        if (in_array($memberInfo->shop->trenchnum, $sellShopTypeArr) && empty($memberInfo->laoBanHao)) {
            // 被绑定者为供应商并且是在老板，需要判断是否有未完成提款的订单
            $unOrder = DpOpderForm::query()
                ->where('shopid', $memberInfo->shopId)
                ->where('addtime', '2016-03-02 00:00:00')
                ->where(function ($query) {
                    $query->whereIn('orderact', [2, 3, 7, 101, 102])
                        ->orWhere(function ($query) {
                            $query->where('method_act', DpOpderForm::ORDER_PAY_METHOD_COMPANY)
                                ->whereIn('orderact', [2, 3, 4, 7, 101, 102]);
                        });
                })
                ->count();
            if ($unOrder > 0) {
                throw new ShopException([], '有订单未完成交易，请先完成所有交易');
            }
        } else {
            // 被绑定者是否有未完成的订单
            $unOrder = DpOpderForm::query()
                ->where('uid', $memberInfo->shId)
                ->whereIn('orderact', [2, 3, 101, 102])
                ->count();
            if ($unOrder > 0) {
                throw new ShopException([], '有订单未完成交易，请先完成所有交易');
            }
            // 是否有未还款的白条
            $limitInfo = $this->iousLimit($memberInfo->shopId, $memberInfo->shId);
            try {
                $limitInfoArr = json_decode($limitInfo, true);
            } catch (\Exception $e) {
                $limitInfoArr['code'] = 1;
            }
            if (0 == $limitInfoArr['code'] && $limitInfoArr['data']['credit_limit']['used']) {
                throw new ShopException([], '还有未完成的贷款信息');
            }
        }

        // 根据店铺ID取得绑定者店铺信息
        $shopSelectArr = [
            'shop'   => ['shopId', 'laoBanId', 'trenchnum',],
            'user'   => ['shId', 'shopId', 'wegroupid', 'shengheAct', 'guanzhuAct',],
            'market' => ['pianquId'],
        ];
        $shopInfo = $this->shopRepo->getShopInfo($shopId, $shopSelectArr);
        if (is_null($shopInfo)) {
            // 店铺不存在
            throw new ShopException(ShopException::SHOP_NOT);
        } elseif ($shopInfo->user[0]->shengheAct != DpShangHuInfo::STATUS_PASS) {
            // 店铺未开通
            throw new ShopException(ShopException::SHOP_CLOSE);
        } elseif (!in_array($shopInfo->trenchnum, [DpShopInfo::YIPI, DpShopInfo::VENDOR])) {
            // 店铺类型不可绑定成员
            throw new ShopException(ShopException::SHOP_NOT_BAND_MEMBER);
        } elseif (empty($shopInfo->user[0]->guanzhuAct)) {
            // 店铺大老板已取消关注此公众号
            throw new ShopException(ShopException::SHOP_BOOS_UNSUBSCRIBE);
        }
        // 判断是否已是此店铺下的成员
        if ($memberInfo->shopId == $shopInfo->shopId) {
            // 已是此店铺下的成员
            throw new ShopException(ShopException::ALREADY_SHOP_MEMBER);
        }
        // 判断是否在绑定自己
        if ($memberInfo->shId == $shopInfo->user[0]->shId) {
            // 自己绑定为自己的成员
            throw new ShopException(ShopException::SHOP_MEMBER_SAME);
        }
        // 进行绑定处理
        $this->bandShopMember($memberInfo, $shopInfo, $role, $boosNum);
    }

    /**
     * 生成店铺成员添加的二维码
     *
     * @param $shopId integer 绑定者店铺ID
     * @param $role   integer 被绑定者成员角色
     *
     * @return array
     * @throws AppException
     * @throws ShopException
     */
    public function getMemberAddCode($shopId, $role)
    {
        // 根据店铺ID取得店铺信息
        $shopSelectArr = [
            'shop'   => ['shopId', 'laoBanId', 'trenchnum',],
            'user'   => ['shId', 'shopId', 'wegroupid', 'shengheAct', 'guanzhuAct',],
            'market' => ['pianquId'],
        ];
        $boosShopInfo = $this->shopRepo->getShopInfo($shopId, $shopSelectArr);
        if (is_null($boosShopInfo)) {
            // 店铺不存在
            throw new ShopException(ShopException::SHOP_NOT);
        } elseif ($boosShopInfo->user[0]->shengheAct != DpShangHuInfo::STATUS_PASS) {
            // 店铺未开通
            throw new ShopException(ShopException::SHOP_CLOSE);
        } elseif (!in_array($boosShopInfo->trenchnum, [DpShopInfo::YIPI, DpShopInfo::VENDOR])) {
            // 店铺类型不可绑定成员
            throw new ShopException(ShopException::SHOP_NOT_BAND_MEMBER);
        } elseif (empty($boosShopInfo->user[0]->guanzhuAct)) {
            // 店铺大老板已取消关注此公众号
            throw new ShopException(ShopException::SHOP_BOOS_UNSUBSCRIBE);
        }

        // 生成绑定二维码图片
        $url = sprintf(
            "%s?m=Admin&c=Interface&a=weChatImgCode",
            config('request_url.wechat_request_url')
        );
        $dataArr = [
            'role'    => $role,
            'boos_id' => $boosShopInfo->user[0]->shId,
        ];
        $signKey = config('signature.wechat_sign_key');
        $requestDataArr = RequestDataEncapsulationUtil::requestDataSign($dataArr, $signKey);

        /** @var HTTPRequestUtil $requestUtil */
        $requestUtil = app(HTTPRequestUtil::class);
        $response = $requestUtil->post($url, $requestDataArr);
        $responseArr = @json_decode($response, true);

        if (!empty($responseArr['code'])) {
            throw new AppException(
                sprintf(
                    "生成失败 :%s",
                    array_get($responseArr, 'message', '未知')
                )
            );
        }

        return [
            'img_code_url' => $responseArr['data']['pic_path'],
        ];
    }

    /**
     * 白条额度获取
     *
     * @param $shopId integer 店铺ID
     * @param $userId integer 会员ID
     *
     * @return string
     */
    protected function iousLimit($shopId, $userId)
    {
        // 请求头中期望返回的数据格式
        $headersArr = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json;  charset=utf-8',
        ];
        // 根据店铺ID取得店铺名称
        $dataArr['shop_info'] = [
            'shop_id' => $shopId,
            'user_id' => $userId,
        ];
        $requestDataArr = RequestDataEncapsulationUtil::getHttpRequestSign(
            $dataArr,
            config('signature.main_sign_key')
        );
        $getShopRequestUrl = config('request_url.main_request_url') . '/buyer/ious/credit-limit';
        $limitInfo = $this->httpRequest->json($getShopRequestUrl, $requestDataArr, $headersArr);

        return $limitInfo;
    }

    /**
     * 做店铺成员的绑定操作
     *
     * @param $memberInfo   \Illuminate\Database\Eloquent\Builder 成员信息
     * @param $boosShopInfo \Illuminate\Database\Eloquent\Builder 老板信息
     * @param $role         integer 成员角色编号
     * @param $boosNum      integer 成员店铺大老板数量
     */
    protected function bandShopMember($memberInfo, $boosShopInfo, $role, $boosNum)
    {
        \DB::connection('mysql_zdp_main')
            ->transaction(function () use ($memberInfo, $boosShopInfo, $role, $boosNum) {
                $updateArr = [
                    'shengheAct' => DpShangHuInfo::STATUS_DELETE,
                    'unionId'    => "{$memberInfo->unionId}-删除-{$memberInfo->shId}",
                    'OpenID'     => "{$memberInfo->OpenID}-删除-{$memberInfo->shId}",
                    'lianxiTel'  => "{$memberInfo->lianxiTel}-删除-{$memberInfo->shId}",
                ];
                $createArr = [
                    'shopId'         => $boosShopInfo->shopId,
                    'OpenID'         => $memberInfo->OpenID,
                    'unionId'        => $memberInfo->unionId,
                    'unionName'      => $memberInfo->unionName,
                    'unionPic'       => $memberInfo->unionPic,
                    'xingming'       => $memberInfo->xingming,
                    'lianxiTel'      => $memberInfo->lianxiTel,
                    'laoBanId'       => $boosShopInfo->laoBanId,
                    'laoBanHao'      => $role,
                    'shengheAct'     => DpShangHuInfo::STATUS_PASS,
                    'endGuanzhuTime' => date('Y-m-d H:i:s'),
                    'wegroupid'      => $boosShopInfo->user[0]->wegroupid,
                ];
                // 修改被绑定者的店铺信息为已删除
                DpShangHuInfo::query()
                    ->where('shId', $memberInfo->shId)
                    ->update($updateArr);
                // 如果把大老板绑定走了，则下面未审核的会员则需要删除
                // 因为会员还未审核使用过，则做物理删除
                if (0 == $memberInfo->laoBanHao) {
                    DpShangHuInfo::query()
                        ->where('shopId', $memberInfo->shopId)
                        ->where(function ($query) {
                            $query->where('shengheAct', DpShangHuInfo::STATUS_UNTREATED)
                                ->orWhere(\DB::raw("unionId=lianxiTel"));
                        })
                        ->delete();
                }
                // 添加一条新的会员记录做为成员绑定
                DpShangHuInfo::create($createArr);
                // 更改微信分组
                if ($memberInfo->wegroupid != $boosShopInfo->user[0]->wegroupid) {
                    $setUserGroupArr = [
                        'openid'     => $memberInfo->OpenID,
                        'to_groupid' => $boosShopInfo->user[0]->wegroupid,
                    ];
                    // 走队列做微信分组处理
                    dispatch(new MemberWeChatGroupAdjust($setUserGroupArr));
                }
            });
    }

    /**
     * 会员微信分组的调整
     *
     * @param array $setUserGroupArr array 分组调整信息 ['openid'=>会员微信OPENID, 'to_groupid'=>分组ID]
     *
     * @throws AppException
     */
    public function memberWweChatGroup(array $setUserGroupArr)
    {
        // 做微信分组
        $dataArr = ['data' => json_encode($setUserGroupArr)];
        $url = sprintf(
            "%s?m=Admin&c=Interface&a=wxUserGroupSet",
            config('request_url.wechat_request_url')
        );
        $signKey = config('signature.wechat_sign_key');
        $requestDataArr = RequestDataEncapsulationUtil::requestDataSign($dataArr, $signKey);

        /** @var HTTPRequestUtil $requestUtil */
        $requestUtil = app(HTTPRequestUtil::class);
        $response = $requestUtil->post($url, $requestDataArr);
        $logPath = storage_path('logs') . 'whchat' . date('Y-m-d') . '.log';
        fileLogWrite("对会员({$setUserGroupArr['openid']})进行微信分组调整，返回：{$response}", $logPath);
        $responseArr = @json_decode($response, true);

        if (empty($response) ||
            !empty($responseArr['status']) ||
            !empty($responseArr['errcode'])
        ) {
            throw new AppException(
                sprintf(
                    "分组失败 :%s",
                    array_get($responseArr, 'message', $responseArr['errmsg'])
                )
            );
        }
    }
}
