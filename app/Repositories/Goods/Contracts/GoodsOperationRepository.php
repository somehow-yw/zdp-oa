<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/14
 * Time: 13:32
 */

namespace App\Repositories\Goods\Contracts;

/**
 * Interface GoodsOperationRepository.
 * 商品操作的数据处理 不包括商品添加及修改
 *
 * @package App\Repositories\Goods\Contracts
 */
interface GoodsOperationRepository
{
    /**
     * 商品图片的删除
     *
     * @param $pictureId int 图片ID
     *
     * @return void
     */
    public function delGoodsPicture($pictureId);

    /**
     * 商品检验报告图片的删除
     *
     * @param $pictureId int 图片ID
     *
     * @return void
     */
    public function delGoodsInspectionReport($pictureId);

    /**
     * 下架普通商品
     *
     * @param $goodsId integer 商品id
     *
     * @return mixed
     */
    public function soldOutOrdinaryGoods($goodsId);

    /**
     * 刷新商品价格
     *
     * @param $goodsId integer 商品id
     *
     * @return mixed
     */
    public function refreshOrdinaryGoodsPrice($goodsId);

    /**
     * 删除商品id
     *
     * @param $goodsId integer 商品id
     *
     * @return void
     */
    public function deleteOrdinaryGoods($goodsId);

    /**
     * 上架普通商品
     *
     * @param $goodsId integer 商品id
     *
     * @return void
     */
    public function onSaleOrdinaryGoods($goodsId);

    /**
     * 商品审核通过处理
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function auditPass($goodsId);

    /**
     * 审核拒绝处理
     *
     * @param $goodsId       int 商品ID
     * @param $refusedReason string 拒绝理由
     *
     * @return int
     */
    public function auditRefused($goodsId, $refusedReason);

    /**
     * 恢复删除的商品
     *
     * @param $goodsId integer 商品ID
     *
     * @return void
     */
    public function unDeleteOrdinaryGoods($goodsId);

    /**
     * 根据商品分类ID将商品状态更改为待审核
     *
     * @param $goodsTypeId int 商品分类ID
     *
     * @return int 影响的行数
     */
    public function updateGoodsStatusToNotAudit($goodsTypeId);
}
