<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/25
 * Time: 13:28
 */

namespace App\Services\Goods;

use App\Models\DpGoodsInfo;
use App\Models\DpGoodsOperationLog;
use App\Repositories\Goods\Contracts\GoodsListRepository;

/**
 * Class GoodsListService.
 * 商品列表
 *
 * @package App\Services\Goods
 */
class GoodsListService
{
    private $goodsListRepo;

    public function __construct(GoodsListRepository $goodsListRepo)
    {
        $this->goodsListRepo = $goodsListRepo;
    }

    /**
     * 获取普通商品列表
     *
     * @param               $areaId       integer 片区id
     * @param               $size         integer 获取的数据量
     * @param               $goodsTypeId  integer 商品类型id
     * @param null|integer  $marketId     市场id
     * @param null|integer  $onSaleStatus 商品上下架状态
     * @param null|integer  $auditStatus  商品审核状态
     * @param null|integer  $unit         单位id
     * @param null|integer  $priceStatus  价格状态
     * @param null|integer  $keyWords     关键字
     * @param null|string   $queryBy      通过什么查询
     * @param string        $orderBy      排序字段
     * @param bool          $aesc         是否升序
     * @param bool          $signing      是否签约商品
     *
     * @return mixed
     */
    public function getGoodsList(
        $areaId,
        $size,
        $goodsTypeId,
        $marketId = null,
        $onSaleStatus = null,
        $auditStatus = null,
        $unit = null,
        $priceStatus = null,
        $keyWords = null,
        $queryBy = 'goods_id',
        $orderBy = 'goods_id',
        $aesc = false,
        $signing = false
    ) {
        $goodsList = $this->goodsListRepo->getOrdinaryGoodsList(
            $areaId,
            $size,
            $goodsTypeId,
            $marketId,
            $onSaleStatus,
            $auditStatus,
            $unit,
            $priceStatus,
            $keyWords,
            $queryBy,
            $orderBy,
            $aesc,
            $signing
        );

        return $goodsList;
    }

    /**
     * 待审核商品列表获取
     *
     * @param $shopId      int 店铺ID
     * @param $goodsStatus int 商品状态
     * @param $size        int 获取数量
     * @param $page        int 当前页数
     * @param $sortField   string 排序字段
     * @param $marketId    int 市场ID
     * @param $areaId      int 大区ID
     * @param $signing     boolean 是否只查询签约商品
     *
     * @return array
     */
    public function getNewGoodsList($shopId, $goodsStatus, $size, $page, $sortField, $marketId, $areaId, $signing)
    {
        $columnSelectArr = [
            'goods'          => [
                'id as goods_id',
                'gname as goods_name',
                'addtime as add_time',
                'gengxin_time as update_time',
                'shenghe_act as goods_status',
            ],
            'goodsAttribute' => ['goods_price as goods_price'],
            'shop'           => ['shopId as shop_id', 'dianPuName as shop_name'],
            'market'         => ['pianqu as market_name'],
            'signing'        => ['id as signing_id', 'signing_name'],
        ];
        $goodsInfoObjs = $this->goodsListRepo
            ->getNewGoodsList($shopId, $goodsStatus, $size, $columnSelectArr, $sortField, $marketId, $areaId, $signing);
        $reDataArr = [
            'page'  => (int)$page,
            'total' => $goodsInfoObjs->total(),
            'goods' => [],
        ];
        if ($goodsInfoObjs->count()) {
            $goodsArr = [];
            foreach ($goodsInfoObjs as $goods) {
                $goodsArr[] = [
                    'goods_id'     => $goods->goods_id,
                    'goods_name'   => $goods->goods_name,
                    'goods_price'  => is_null($goods->goodsAttribute) ? 0 : $goods->goodsAttribute->goods_price,
                    'add_time'     => $goods->add_time,
                    'update_time'  => $goods->update_time,
                    'goods_status' => $goods->goods_status,
                    'shops'        => [
                        'shop_id'   => $goods->shop_id,
                        'shop_name' => $goods->shop_name,
                    ],
                    'markets'      => [
                        'market_name' => $goods->market_name,
                    ],
                    'on_signing'   => !$goods->signing->isEmpty(),
                ];
            }
            $reDataArr['goods'] = $goodsArr;
        }

        return $reDataArr;
    }

    /**
     * 获取商品历史价格列表
     *
     * @param $goodsId int 商品ID
     * @param $page    int 请求页数
     * @param $size    int 获取数量
     *
     * @return array
     */
    public function getHistoryPricesList($goodsId, $page, $size)
    {
        $columnSelectArr = [
            'edit_time as change_time',
            'new_price as goods_price',
        ];
        $historyPriceObjs = $this->goodsListRepo->getHistoryPricesList($goodsId, $columnSelectArr);
        $historyPriceArr = $historyPriceObjs->unique('date')->toArray();
        $listTotal = count($historyPriceArr);
        // 计算获取数据偏移
        $offset = ($page - 1) * $size;
        if ($offset > $listTotal) {
            $offset = $listTotal;
        }
        $reDataArr = [
            'page'  => (int)$page,
            'total' => $listTotal,
            'logs'  => [],
        ];
        if ($listTotal) {
            $reDataArr['logs'] = array_slice($historyPriceArr, $offset, $size);
        }

        return $reDataArr;
    }

    /**
     * 获取商品操作日志列表
     *
     * @param $goods_id integer 商品id
     * @param $page     integer 当前页数
     * @param $size     integer 获取的数据量
     *
     * @return  array
     */
    public function getGoodsOperationLogsList($goods_id, $page, $size)
    {
        $logsInDB = $this->goodsListRepo->getGoodsOperationLogs($goods_id, $size);
        $logsList = [];
        foreach ($logsInDB as $logInDB) {
            $log = [
                'operate_time' => $logInDB->created_at,
                'type'         => $logInDB->type,
                'note'         => $logInDB->note,
                'identity'     => $logInDB->identity,
                'user_name'    => $logInDB->user_name,
            ];
            $logsList[] = $log;
        }
        $reDataArr = [
            'page'  => (int)$page,
            'total' => $logsInDB->total(),
            'logs'  => $logsList,
        ];

        return $reDataArr;
    }
}
