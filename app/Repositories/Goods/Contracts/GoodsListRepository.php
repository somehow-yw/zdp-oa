<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/25
 * Time: 13:36
 */

namespace App\Repositories\Goods\Contracts;

/**
 * Interface GoodsListRepository.
 * 商品列表
 *
 * @package App\Repositories\Goods\Contracts
 */
interface GoodsListRepository
{
    /**
     * 获取普通商品列表
     *
     * @param      $areaId           integer 片区id
     * @param      $size             integer 需要获取的数据大小
     * @param      $goodsTypeId      integer 商品分类id
     * @param      $marketId         integer 市场id
     * @param      $onSaleStatus     integer 商品上下架状态
     * @param      $auditStatus      integer 商品审核状态
     * @param      $unit             integer 单位id
     * @param      $priceStatus      integer 价格状态
     * @param      $keyWords         string  关键字
     * @param      $queryBy          string  通过什么查询(商品名goods_name 供应商supplier_name 品牌brand)
     * @param      $orderBy          string  排序字段
     * @param      $aesc             boolean 是否升序
     * @param bool $signing          是否签约商品
     *
     * @return array
     */
    public function getOrdinaryGoodsList(
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

    /**
     * 待审核商品列表获取
     *
     * @param       $shopId          int 店铺ID
     * @param       $goodsStatus     int 商品状态
     * @param       $size            int 获取数量
     * @param array $columnSelectArr array 需要的列信息
     *
     *                               [
     *                                  'goods'=>[...],  商品
     *                                  'goodsAttribute'=>[...],  商品基本属性
     *                                  'shop'=>[...],  店铺
     *                                  'market'=>[...],  市场
     *                               ]
     *
     * @param       $sortField       string 排序字段
     * @param       $marketId        int 市场ID
     * @param       $areaId          int 大区ID
     * @param       $signing         boolean 是否只查询签约商品
     *
     * @return object
     */
    public function getNewGoodsList(
        $shopId,
        $goodsStatus,
        $size,
        array $columnSelectArr,
        $sortField,
        $marketId,
        $areaId,
        $signing
    );

    /**
     * 获取商品历史价格列表
     *
     * @param       $goodsId          int 商品ID
     * @param array $columnSelectArr  array 需要的列信息 格式：['...','...']
     *
     * @return object
     */
    public function getHistoryPricesList($goodsId, array $columnSelectArr);

    /**
     * 获取商品操作日志列表
     *
     * @param $goodsId integer 商品id
     * @param $size    integer 获取的数据大小
     *
     * @return array
     */
    public function getGoodsOperationLogs($goodsId, $size);
}
