<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/19
 * Time: 18:40
 */

namespace App\Repositories\Shops;

use DB;

use App\Repositories\Shops\Contracts\ShopRepository as RepositoriesContract;

use App\Models\DpShopInfo;
use Zdp\Main\Data\Models\DpShopInfo as ShopInfo;
use App\Models\DpShangHuInfo;
use App\Models\DpGoodsInfo;
use \Zdp\Main\Data\Models\DpShangHuInfo as DpMemberInfo;
use Zdp\Main\Data\Models\DpShopRankRule;
use Zdp\Main\Data\Models\DpShopScore;
use Zdp\Main\Data\Models\DpShopScoreLog;
use Zdp\Main\Data\Models\DpShopScoreRule;

class ShopRepository implements RepositoriesContract
{
    /**
     * 店铺信息查询
     *
     * @see \App\Repositories\Shops\Contracts\ShopRepository::getShopInfo()
     *
     * @param       $shopId          int 店铺ID
     * @param array $columnSelectArr array 所需获取字段
     *
     *                               [
     *                                  'shop'=>[], 店铺
     *                                  'user'=>[], 会员（大老板）
     *                                  'market'=>[], 市场
     *                               ]
     *
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    public function getShopInfo($shopId, array $columnSelectArr)
    {
        $shopSelectArr = array_merge($columnSelectArr['shop'], ['shopId', 'pianquId']);
        $userSelectArr = array_merge($columnSelectArr['user'], ['shopId']);
        $marketSelectArr = array_merge($columnSelectArr['market'], ['pianquId']);
        $shopInfoObj = ShopInfo::query()
            ->with(
                [
                    'user'   => function ($query) use ($userSelectArr) {
                        $query->where('laoBanHao', DpShangHuInfo::SHOP_BOOS)
                            ->select($userSelectArr);
                    },
                    'market' => function ($query) use ($marketSelectArr) {
                        $query->select($marketSelectArr);
                    },
                ]
            )
            ->where('shopId', $shopId)
            ->where('state', DpShopInfo::STATE_NORMAL)
            ->select($shopSelectArr)
            ->first();

        return $shopInfoObj;
    }

    /**
     * 取得有待审核商品的店铺及待审核商品数量
     *
     * @see \App\Repositories\Shops\Contracts\ShopRepository::getShopInfoByMarket()
     *
     * @param       $marketId        int 市场ID
     * @param array $columnSelectArr array 所需获取字段
     *
     *                          [
     *                              'aggregation'=>[...]
     *                              'goods'=>[...],
     *                              'shop'=>[...],
     *                          ]
     *
     * @return array
     */
    public function getShopInfoByMarket($marketId, array $columnSelectArr)
    {
        $fieldsArr = [];
        foreach ($columnSelectArr as $key => $fields) {
            if (count($fields)) {
                foreach ($fields as $field) {
                    if ('aggregation' != $key) {
                        $fieldsArr[] = "{$key}.{$field}";
                    } else {
                        $fieldsArr[] = DB::raw($field);
                    }
                }
            }
        }
        $goodsStatusArr = [
            DpGoodsInfo::STATUS_AUDIT,
            DpGoodsInfo::STATUS_REJECT,
            DpGoodsInfo::STATUS_MODIFY_AUDIT,
        ];
        $shopInfoArr = DB::connection('mysql_zdp_main')->table('dp_shopInfo as shop')
            ->join('dp_goods_info as goods', 'shop.shopId', '=', 'goods.shopid')
            ->where('shop.pianquId', $marketId)
            ->whereIn('goods.shenghe_act', $goodsStatusArr)
            ->select($fieldsArr)
            ->groupBy('shop.shopId')
            ->get();

        return $shopInfoArr;
    }

    /**
     * 根据会员注册电话取得会员信息及对应店铺信息
     *
     * @param       $mobile    string 注册电话
     * @param array $selectArr array 所需获取的字段名
     *
     *                         [
     *                             'member' => ['shId', ...] // 成员信息
     *                             'shop' => ['shopId', ...] // 店铺信息
     *                         ]
     *
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    public function getMemberInfoByMobile($mobile, array $selectArr)
    {
        $shopSelectArr = array_merge($selectArr['shop'], ['shopId']);
        $memberSelectArr = array_merge($selectArr['member'], ['shopId']);

        return DpMemberInfo::query()
            ->with([
                'shop' => function ($query) use ($shopSelectArr) {
                    $query->select($shopSelectArr);
                },
            ])
            ->where('lianxiTel', $mobile)
            ->where('shengheAct', '!=', DpMemberInfo::STATUS_DELETE)
            ->first($memberSelectArr);
    }

    /**
     * @see \App\Repositories\Shops\Contracts\ShopRepository::getShop
     */
    public function getShop($shopId)
    {
        $shopInfo = DpShopInfo::with(
            [
                'user' => function ($query) {
                    $query->where('laoBanHao', DpShangHuInfo::SHOP_BOOS);
                },
            ]
        )
                              ->where('shopId', $shopId)
                              ->first();

        return $shopInfo;
    }

    /**
     * 根据店铺分数类型取得分数操作规则
     *
     * @see \App\Repositories\Contracts\ShopRepository::getShopScoreOperatorRule()
     *
     * @param int $scoreNo 分数操作类型
     *
     * @return object
     * @throws \Exception
     */
    public function getShopScoreOperatorRule($scoreNo)
    {
        $shopScoreOperatorRule = DpShopScoreRule::where('score_no', $scoreNo)
                                                ->first();
        if (!$shopScoreOperatorRule) {
            throw new \Exception('店铺等级分数规则不存在');
        }

        return $shopScoreOperatorRule;
    }

    /**
     * 根据店铺等级分数类型取得对应店铺已获得的总分数
     *
     * @see \App\Repositories\Contracts\ShopRepository::getShopScoreTypeExistingScore()
     *
     * @param int $shopId  店铺ID
     * @param int $scoreNo 店铺等级分数类型
     *
     * @return int
     */
    public function getShopScoreTypeExistingScore($shopId, $scoreNo)
    {
        return DpShopScoreLog::where('shop_id', $shopId)
                             ->where('score_no', $scoreNo)
                             ->sum('score');
    }

    /**
     * 根据店铺ID取得等级信息
     *
     * @see \App\Repositories\Contracts\ShopRepository::getShopScore()
     *
     * @param int $shopId 店铺ID
     *
     * @return object
     */
    public function getShopScore($shopId)
    {
        $shopScoreInfo = DpShopScore::where('shop_id', $shopId)
                                    ->first();

        if (!$shopScoreInfo) {
            $createShopScoreArr = [
                'shop_id'         => $shopId,
                'rank_score'      => 0,
                'shop_rank'       => DpShopRankRule::LEAST_RANK,
                'appraise_score'  => 0,
                'appraise_number' => 0,
            ];
            $shopScoreInfo = DpShopScore::create($createShopScoreArr);
        }

        return $shopScoreInfo;
    }

    /**
     * 修改店铺等级分数，同时写入对应的日志
     *
     * @see \App\Repositories\Contracts\ShopRepository::updateShopScore()
     *
     * @param int    $shopId            卖家店铺ID
     * @param int    $scoreNo           分数操作类型
     * @param int    $score             操作分数
     * @param int    $shopOperatorScore 当前获得或减扣分数
     * @param int    $shopRank          店铺当前等级
     * @param int    $operatorId        操作者ID
     * @param int    $operatorType      操作者类型
     * @param string $remark            操作备注
     */
    public function updateShopScore(
        $shopId,
        $scoreNo,
        $score,
        $shopOperatorScore,
        $shopRank,
        $operatorId,
        $operatorType,
        $remark
    ) {
        $updateArr = [
            'rank_score' => $score,
            'shop_rank'  => $shopRank,
        ];
        $createArr = [
            'shop_id'       => $shopId,
            'score_no'      => $scoreNo,
            'score'         => $shopOperatorScore,
            'operator_type' => $operatorType,
            'operator_id'   => $operatorId,
            'remark'        => $remark,
        ];

        DB::transaction(
            function () use (
                $updateArr,
                $createArr,
                $shopId
            ) {
                DpShopScore::where('shop_id', $shopId)
                           ->update($updateArr);

                DpShopScoreLog::create($createArr);
            }
        );
    }

    /**
     * 得到当前分数对应的等级信息
     *
     * @see \App\Repositories\Contracts\ShopRepository::getShopRank()
     *
     * @param int $shopRankScore 当前店铺分数
     *
     * @return object
     */
    public function getShopRank($shopRankScore)
    {
        $shopRankRuleObj = DpShopRankRule::where('small_rank_score', '<=', $shopRankScore)
                                         ->where('max_rank_score', '>=', $shopRankScore)
                                         ->orderBy('small_rank_score', 'asc')
                                         ->first();
        if (!$shopRankRuleObj) {
            // 是否小于最低分
            $shopRankRuleObj = DpShopRankRule::where('small_rank_score', '>=', $shopRankScore)
                                             ->orderBy('small_rank_score', 'asc')
                                             ->first();
        }
        if (!$shopRankRuleObj) {
            // 是否大于最高分
            $shopRankRuleObj = DpShopRankRule::where('small_rank_score', '<=', $shopRankScore)
                                             ->orderBy('small_rank_score', 'desc')
                                             ->first();
        }

        return $shopRankRuleObj;
    }
}
