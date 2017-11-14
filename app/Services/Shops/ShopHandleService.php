<?php

namespace App\Services\Shops;

use App\Exceptions\AppException;
use App\Services\SendWeChatMessageService;
use Carbon\Carbon;
use Zdp\Main\Data\Models\DpCartInfo;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\Main\Data\Models\DpGoodsSigning;
use Zdp\Main\Data\Models\DpMarketInfo;
use Zdp\Main\Data\Models\DpPianquDivide;
use Zdp\Main\Data\Models\DpShangHuInfo;
use Zdp\Main\Data\Models\DpShopCart;
use Zdp\Main\Data\Models\DpShopInfo;
use Zdp\Main\Data\Services\Areas;
use Zdp\Search\Services\ElasticService;

class ShopHandleService
{
    /**
     * 店铺列表
     *
     * @param integer $provinceId 搜索内容
     * @param string  $search     搜索内容
     * @param string  $searchType 搜索类型
     * @param integer $shengheAct 店铺审核状态
     * @param integer $page       请求页
     * @param integer $size       单页数据量
     *
     * @return array
     */
    public function index(
        $provinceId,
        $search,
        $searchType,
        $shengheAct,
        $page,
        $size
    ) {
        $query = DpShangHuInfo
            ::leftJoin('dp_shopInfo as shop', 'shop.shopId', '=', 'dp_shangHuInfo.shopId')
            ->join('dp_pianqu as p', 'p.pianquId', '=', 'shop.pianquId')
            ->where('dp_shangHuInfo.laoBanHao', 0)
            ->where('dp_shangHuInfo.shengheAct', $shengheAct);

        if ($provinceId) {
            // 获取省名称
            $region = Areas::$areas;
            $province = $region['province'][$provinceId];
            $query->where('shop.province', $province);
        }
        if (!empty($searchType) && !empty($search)) {
            switch ($searchType) {
                case 'mobile':
                    $query->where('dp_shangHuInfo.lianxiTel', $search);
                    break;
                case 'user_name':
                    $query->where('dp_shangHuInfo.xingming', 'like', '%' . $search . '%');
                    break;
                case 'shop_name':
                    $query->where('shop.dianPuName', 'like', '%' . $search . '%');
                    break;
            }
        }
        $pagers = $query->select([
            'dp_shangHuInfo.shId',
            'dp_shangHuInfo.xingming',
            'dp_shangHuInfo.lianxiTel',
            'dp_shangHuInfo.zhuceTime',
            'dp_shangHuInfo.grounds',

            'shop.shopId',
            'shop.trenchnum',
            'shop.dianPuName',
            'shop.xiangXiDiZi',
            'shop.province_id',
            'shop.province',
            'shop.city_id',
            'shop.city',
            'shop.county_id',
            'shop.county',
            'shop.pianquId',

            'p.province as pianqu_province',
            'p.pianqu as pianqu_name',
        ])
            ->orderBy('shopId', 'desc')
            ->paginate($size, ['*'], null, $page);

        return [
            'detail'    => $pagers->items(),
            'current'   => $pagers->currentPage(),
            'last_page' => $pagers->lastPage(),
            'total'     => $pagers->total(),
        ];
    }

    /**
     * 获取店铺信息
     *
     * @param $shopId
     *
     * @return array
     */
    public function show($shopId)
    {
        $shopInfo = DpShangHuInfo::leftJoin('dp_shopInfo as shop', 'shop.shopId', '=', 'dp_shangHuInfo.shopId')
            ->join('dp_pianqu as p', 'p.pianquId', '=', 'shop.pianquId')
            ->where('dp_shangHuInfo.laoBanHao', 0)
            ->where('shop.shopId', $shopId)
            ->select([
                'dp_shangHuInfo.shId',
                'dp_shangHuInfo.xingming',
                'dp_shangHuInfo.lianxiTel',
                'dp_shangHuInfo.zhuceTime',
                'dp_shangHuInfo.wegroupid as weChatGroupId',

                'shop.shopId',
                'shop.trenchnum',
                'shop.dianPuName',
                'shop.cardPic',
                'shop.xiangXiDiZi',
                'shop.province_id',
                'shop.province',
                'shop.city_id',
                'shop.city',
                'shop.county_id',
                'shop.county',
                'shop.dianpuJianJie as main_products',
                'shop.pianquId',
                'shop.head_portrait',
                'shop.jieDanTel',
                'shop.signing_type',
                'shop.signing_goods_num',
                'shop.signing_balance',
                'shop.signing_time',

                'p.province as pianqu_province_id',
                'p.pianqu as pianqu_name',
            ])
            ->first();

        if (!is_null($shopInfo)) {
            if (empty($shopInfo->cardPic)) {
                $shopInfo->cardPic = '/Public/images/buyer-cli/default-img1.png';
            }
            $shopInfo->signing_type_lists = DpShopInfo::getSigningType();
        }

        return $shopInfo->toArray();
    }

    /**
     * 店铺信息修改
     *
     * @param $updateArr
     *
     * @throws AppException
     */
    public function update($updateArr)
    {
        // 验证当前shId是否属于当前店铺id
        $userInfo = DpShangHuInfo::where('shopId', $updateArr['shopId'])
            ->where('shId', $updateArr['shId'])
            ->where('laoBanHao', DpShangHuInfo::SHOP_BOOS)
            ->first();
        if (!$userInfo) {
            throw new AppException('传入用户信息非此店铺老板');
        }
        // 根据传入片区id获取片区对应的id
        $pianquProvinceId = DpMarketInfo::where('pianquId', $updateArr['pianquId'])
            ->value('province');
        // 供应商类型
        $sellMarkerArr = [
            DpShopInfo::YIPI,
            DpShopInfo::VENDOR,
        ];
        if (in_array($updateArr['trenchnum'], $sellMarkerArr) && $pianquProvinceId != $updateArr['province_id']) {
            throw new AppException('一批商地址与大区数据传入错误');
        }
        // 是否是一批传入大区
        if (!in_array($updateArr['trenchnum'], $sellMarkerArr) && !empty($updateArr['pianquId'])) {
            throw new AppException('非一批商没有大区');
        }
        // 传入一批商大区是否属于该省份
        if (in_array($updateArr['trenchnum'], $sellMarkerArr)) {
            // 传入大区是否属于该省
            $province = DpMarketInfo::where('pianquId', $updateArr['pianquId'])
                ->where('province', $updateArr['province_id'])
                ->first();
            if (empty($province)) {
                throw new AppException('市场所在地址与店铺地址不相符');
            }
        }
        // 判断传入省id是否和字段匹配
        $regio = Areas::$areas;
        $provinceName = $regio['province'][$updateArr['province_id']];
        if ($provinceName != $updateArr['province']) {
            throw new AppException('传入省名称与id不匹配');
        }

        // 进行信息更改
        \DB::connection('mysql_zdp_main')->transaction(function () use (
            $userInfo,
            $updateArr
        ) {
            // 进行微信分组的设置
            $setWechatGroup = \App::make(SendWeChatMessageService::class);
            $isSuccess = $setWechatGroup->setWechatGroup([
                'openid_list' => [$userInfo->OpenID],
                'to_groupid'  => $updateArr['weChatGroupId'],
            ]);
            if ($isSuccess == 0) {
                throw new AppException('微信分组设置失败');
            }
            // 获取当前店铺信息
            $shopInfo = DpShopInfo::query()->where('shopId', $updateArr['shopId'])
                ->first(['shopId', 'laoBanId', 'signing_type']);
            // 店铺信息修改
            $shopSaveArr = [
                'dianPuName'        => $updateArr['dianPuName'],
                'trenchnum'         => $updateArr['trenchnum'],
                'xiangXiDiZi'       => $updateArr['xiangXiDiZi'],
                'province_id'       => $updateArr['province_id'],
                'province'          => $updateArr['province'],
                'city_id'           => $updateArr['city_id'],
                'city'              => $updateArr['city'],
                'county_id'         => $updateArr['county_id'],
                'county'            => $updateArr['county'],
                'cardPic'           => $updateArr['cardPic'],
                'pianquId'          => $updateArr['pianquId'],
                'dianpuJianJie'     => empty($updateArr['main_products']) ? '' : $updateArr['main_products'],
                'head_portrait'     => $updateArr['head_portrait'],
                'jieDanTel'         => $updateArr['jieDanTel'],
                'signing_type'      => $updateArr['signing_type'],
                'signing_goods_num' => $updateArr['signing_goods_num'],
                'signing_balance'   => $updateArr['signing_balance'],
            ];
            if ($updateArr['signing_type'] == DpShopInfo::NOT_SIGNING) {
                $shopSaveArr['signing_time'] = null;
                // 删除店铺所有已签约商品
                DpGoodsSigning::query()
                    ->where('shop_id', $updateArr['shopId'])
                    ->delete();
                $shopSaveArr['signing_goods_num'] = 0;
            } elseif ($shopInfo->signing_type != DpShopInfo::NOT_SIGNING) {
                $shopSaveArr['signing_time'] = Carbon::now()->format('Y-m-d H:i:s');
            }
            DpShopInfo::where('shopId', $updateArr['shopId'])
                ->update($shopSaveArr);

            // 用户信息修改
            DpShangHuInfo::where('shId', $updateArr['shId'])
                ->update([
                    'xingming'  => $updateArr['xingming'],
                    'wegroupid' => $updateArr['weChatGroupId'],
                ]);
            // 更新店铺搜索索引
            /** @var ElasticService $elasticService */
            $elasticService = app()->make(ElasticService::class);
            $elasticService->updateShop($updateArr['shopId']);
        });
    }

    /**
     * 店铺关闭
     *
     * @param $shopId
     */
    public function close($shopId)
    {
        //查询出该店铺下所有成员信息
        $userList = DpShangHuInfo::where('shopId', $shopId)
            ->where('laoBanHao', '<>', 0)
            ->get();
        // 老板id
        $boss = DpShangHuInfo::where('shopId', $shopId)
            ->where('laoBanHao', 0)
            ->first();
        // 启动事务
        \DB::connection('mysql_zdp_main')->transaction(function () use (
            $boss,
            $userList,
            $shopId
        ) {
            /** @var ElasticService $elasticService */
            $elasticService = app()->make(ElasticService::class);
            // 老板状态为关闭
            $boss->shengheAct = DpShangHuInfo::STATUS_CLOSE;
            $boss->save();
            // 关闭店铺
            DpShopInfo::where('shopId', $shopId)->update(['state' => DpShopInfo::STATE_DEL]);
            // 删除所有成员
            if (!empty($userList)) {
                foreach ($userList as $userInfo) {
                    // 删除成员
                    $dataUser = [
                        'shengheAct' => DpShangHuInfo::STATUS_DELETE,
                        'lianxiTel'  => $userInfo->lianxiTel . '--已删除--' . $userInfo->shId,
                        'unionId'    => $userInfo->unionId . '--已删除--' . $userInfo->shId,
                        'OpenID'     => $userInfo->OpenID . '--已删除--' . $userInfo->shId,
                    ];
                    DpShangHuInfo::where('shId', $userInfo->shId)->update($dataUser);
                    // 删除成员购物车
                    $this->delCart($userInfo->shId);
                }
            }
            // 查询该店铺有无添加的商品
            $goodsList = DpGoodsInfo::where('shopid', $shopId)->select('id')->get();
            if (!empty($goodsList)) {
                // 将该店铺下的商品做删除处理
                DpGoodsInfo::where('shopId', $shopId)
                    ->update(['shenghe_act' => DpGoodsInfo::STATUS_DEL]);
                foreach ($goodsList as $goodsId) {
                    // 将购物车中的商品做删除处理
                    DpCartInfo::where('goodid', $goodsId)
                        ->update(['good_yact' => DpCartInfo::YACT_DEL]);
                    // 更新商品搜索索引
                    $elasticService->updateGoods($shopId);
                }
            }
            // 更新店铺搜索索引
            $elasticService->updateShop($shopId);
        });
    }

    // 删除购物车
    protected function delCart($shId)
    {
        $cartNumber = DpShopCart::where('uid', $shId)->value('cart_number');
        if (!empty($cartNumber)) {
            //删除购物车
            DpShopCart::where('uid', $shId)->delete();
            //删除购物车中未生成订单的商品
            DpCartInfo::where('coid', $cartNumber)->delete();
        }
    }
}