<?php

namespace App\Services\Market;

use App\Exceptions\AppException;
use App\Models\DpMarketInfo;
use App\Models\DpPianquDivide;
use App\Models\DpShopInfo;
use Illuminate\Contracts\Auth\Guard;
use App\Models\User;
use Zdp\Main\Data\Services\Areas;

class MarketService
{
    // 不存在市县数据的省id
    protected $onlyProvinceId = [32, 33, 34];
    // 直辖市id
    protected $onlyCityId = [1, 2, 9, 31];

    /**
     * 获取开通省列表
     *
     * @param $page
     * @param $size
     *
     * @return array
     */
    public function index($page, $size)
    {
        $pagers = DpPianquDivide::where('id', '>', 1)
                                ->paginate($size, ['*'], null, $page);
        $reArr = [];
        foreach ($pagers->items() as $divide) {
            // 获取卖家市场数
            $sellerMarketNum = self::getSellerMarketNum($divide->id);
            // 获取买家区域数
            $buyerCountyNum = self::getBuyerCountyNum($divide->id);
            // 获取卖家数量
            $sellerNum = self::getShopNum($divide->id);
            // 获取买家数量
            $buyerNum = self::getShopNum($divide->id, true);
            $reArr[] = [
                'open_time'         => $divide->created_at,
                'divide_id'         => $divide->id,
                'dividename'        => $divide->dividename,
                'province_id'       => $divide->provinceidtxt,
                'seller_market_num' => $sellerMarketNum,
                'buyer_county_num'  => $buyerCountyNum,
                'seller_num'        => $sellerNum,
                'buyer_num'         => $buyerNum,
            ];
        }

        return [
            'detail'    => $reArr,
            'current'   => $pagers->currentPage(),
            'last_page' => $pagers->lastPage(),
            'total'     => $pagers->total(),
        ];
    }

    // 获取卖家市场数
    protected function getSellerMarketNum($divideid)
    {
        return DpMarketInfo::where('divideid', $divideid)
                           ->selectRaw('COUNT(`pianquId`) AS num')
                           ->value('num');

    }

    // 获取买家区域数
    protected function getBuyerCountyNum($divideid)
    {
        // 获取当前区域所有存在的id数
        $divideids = DpMarketInfo::where('divideid', $divideid)
                                 ->select('pianquId')->get()->toArray();

        return DpShopInfo
            ::whereIn('pianquId', array_dot($divideids))
            ->where('trenchnum', '<>', DpShopInfo::YIPI)
            ->selectRaw('COUNT(DISTINCT `county`) as num')
            ->value('num');
    }

    // 获取店铺数量
    protected function getShopNum($divideid, $isBuyer = false)
    {
        $query = DpMarketInfo
            ::join('dp_shopInfo as s', 's.pianquId', '=', 'dp_pianqu.pianquId')
            ->where('dp_pianqu.divideid', $divideid)
            ->where('state', DpShopInfo::STATE_NORMAL);
        if ($isBuyer) {
            $query->where('trenchnum', '<>', DpShopInfo::YIPI);
        } else {
            $query->where('trenchnum', DpShopInfo::YIPI);
        }

        return $query->selectRaw('COUNT(`shopId`) as num')
                     ->value('num');
    }

    /**
     * 查看开通详情
     *
     * @param integer $id   区域id(dp_pianqu_divided.id)
     * @param integer $page 请求页数
     * @param integer $size 单页数据量
     *
     * @return array
     */
    public function show($id, $page, $size)
    {
        $region = Areas::$areas;
        $divides = DpMarketInfo::where('divideid', $id)
                               ->orderBy('city', 'asc')
                               ->paginate($size, ['*'], null, $page);
        $reArr = [];
        foreach ($divides->items() as $divide) {
            // 处理不存在市县区域
            if (in_array($divide->province,$this->onlyProvinceId))
            {
                $city = '';
                $county = '';
            } elseif (in_array($divide->province,$this->onlyCityId))
            {
                $city = $region['city'][$divide->province][$divide->city];
                $county = '';
            }else{
                $city = $region['city'][$divide->province][$divide->city];
                $county = $region['county'][$divide->province][$divide->city][$divide->county];
            }
            $reArr[] = [
                'pianqu_id' => $divide->pianquId,
                'province'  => $region['province'][$divide->province],
                'city'      => $city,
                'county'    => $county,
                'pianqu'    => $divide->pianqu,
                'addTime'   => $divide->addTime,
            ];
        }

        // 此处返回省id，便于进行市场添加
        $pid = DpPianquDivide::where('id', $id)->value('provinceidtxt');

        return [
            'province_id' => $pid,
            'detail'      => $reArr,
            'current'     => $divides->currentPage(),
            'last_page'   => $divides->lastPage(),
            'total'       => $divides->total(),
        ];
    }

    /**
     * 开通省份
     *
     * @param integer $pid 省份id
     *
     * @throws AppException
     */
    public function openProvince($pid)
    {
        $all = Areas::$areas;
        $name = $all['province'][$pid];

        DpPianquDivide::create([
            'dividename'    => $name,
            'provinceidtxt' => $pid,
        ]);
    }

    /**
     * 新增市场
     *
     * @param integer $pid      省id
     * @param integer $cid      市id
     * @param integer $countyId 区县id
     * @param string  $name     市场名字
     *
     * @throws AppException
     */
    public function openMarket($pid, $cid, $countyId, $name)
    {
        /** @var $auth Guard */
        $auth = \App::make(Guard::class);
        /** @var User $user */
        $user = $auth->user();
        $adminId = $user->id;
        $adminName = $user->user_name;
        $beizhu = '后台添加';
        $divideid = DpPianquDivide::where('provinceidtxt', $pid)->value('id');

        // 处理不存在市县区域
        if (in_array($pid, $this->onlyProvinceId))
        {
            $cid = 0;
            $countyId = 0;
        } elseif (in_array($pid, $this->onlyCityId))
        {
            $countyId = 0;
        }

        // 市场名重复性判断
        $isCopy = DpMarketInfo::where('province', $pid)
                              ->where('city', $cid)
                              ->where('county', $countyId)
                              ->where('divideid', $divideid)
                              ->value('pianqu');
        if ($isCopy) {
            throw new AppException('该区域下已经存在此市场');
        }

        DpMarketInfo::create([
            'province'  => $pid,
            'city'      => $cid,
            'county'    => $countyId,
            'pianqu'    => $name,
            'beizhu'    => $beizhu,
            'adminId'   => $adminId,
            'adminName' => $adminName,
            'divideid'  => $divideid,
        ]);
    }
}