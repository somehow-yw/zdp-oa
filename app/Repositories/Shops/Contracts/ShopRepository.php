<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/19
 * Time: 18:40
 */

namespace App\Repositories\Shops\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ShopRepository
{
    /**
     * 店铺信息查询
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
    public function getShopInfo($shopId, array $columnSelectArr);

    /**
     * 取得有待审核商品的店铺及待审核商品数量
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
    public function getShopInfoByMarket($marketId, array $columnSelectArr);

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
    public function getMemberInfoByMobile($mobile, array $selectArr);

    /**
     * 根据店铺ID取得此店铺信息
     *
     * @param int $shopId 店铺ID
     *
     * @return Collection
     */
    public function getShop($shopId);

    /**
     * 根据店铺分数类型取得分数操作规则
     *
     * @param int $scoreNo 分数操作类型
     *
     * @return object
     */
    public function getShopScoreOperatorRule($scoreNo);

    /**
     * 根据店铺等级分数类型取得对应店铺已获得的总分数
     *
     * @param int $shopId  店铺ID
     * @param int $scoreNo 店铺等级分数类型
     *
     * @return int
     */
    public function getShopScoreTypeExistingScore($shopId, $scoreNo);

    /**
     * 根据店铺ID取得等级信息
     *
     * @param int $shopId 店铺ID
     *
     * @return object
     */
    public function getShopScore($shopId);

    /**
     * 修改店铺等级分数，同时写入对应的日志
     *
     * @param int    $shopId            卖家店铺ID
     * @param int    $scoreNo           分数操作类型
     * @param int    $score             操作的分数
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
    );

    /**
     * 得到当前分数对应的等级信息
     *
     * @param int $shopRankScore 当前店铺分数
     *
     * @return object
     */
    public function getShopRank($shopRankScore);
}
