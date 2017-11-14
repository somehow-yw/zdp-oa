<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/27/16
 * Time: 6:31 PM
 */

namespace App\Services\Goods;

use App\Exceptions\AppException;
use App\Models\DpGoodsConstraints;
use App\Repositories\Goods\Contracts\GoodsConstraintsRepository;
use App\Repositories\Goods\Contracts\GoodsOperationRepository;
use DB;

class GoodsConstraintsService
{
    protected $goodsRepo;
    protected $goodsOperationRepo;

    public function __construct(
        GoodsConstraintsRepository $goodsRepository,
        GoodsOperationRepository $goodsOperationRepo
    ) {
        $this->goodsRepo = $goodsRepository;
        $this->goodsOperationRepo = $goodsOperationRepo;
    }

    /**
     * 获取商品类型基本属性
     *
     * @param $type_id integer
     *
     * @return array
     *
     * @throws AppException
     */
    public function getGoodsBasicAttr($type_id)
    {
        return $this->goodsRepo->getGoodsBasicAttr($type_id);
    }

    /**
     * 更新或者添加商品类型基本属性
     *
     * @param $typeId         integer 商品分类类型id
     * @param $typeConstraint array 商品分类型号约束
     * @param $specConstraint array 商品分类规格约束
     *
     * @return void
     */
    public function updateGoodsBasicAttr($typeId, $typeConstraint, $specConstraint)
    {
        //更新或添加商品类型型号约束
        DB::connection('mysql_zdp_main')->transaction(
            function () use (
                $typeId,
                $typeConstraint,
                $specConstraint
            ) {
                $this->goodsRepo->updateGoodsBasicAttr(
                    $typeId,
                    DpGoodsConstraints::TYPE_CONSTRAINT,
                    $typeConstraint['format_type_id'],
                    $typeConstraint['format_values']
                );

                //更新或添加商品类型规格约束
                $this->goodsRepo->updateGoodsBasicAttr(
                    $typeId,
                    DpGoodsConstraints::SPEC_CONSTRAINT,
                    $specConstraint['format_type_id'],
                    $specConstraint['format_values']
                );
                $this->goodsOperationRepo->updateGoodsStatusToNotAudit($typeId);
            }
        );
    }

    /**
     * @param $typeId         integer 商品分类id
     * @param $constraintType integer 商品约束类型
     *                        规格约束
     *                        DpGoodsConstraints::SPEC_CONSTRAINT
     *                        or
     *                        类型约束
     *                        DpGoodsConstraints::TYPE_CONSTRAINT
     *
     * @return DpGoodsConstraints|null
     */
    public function getGoodsConstraint($typeId, $constraintType)
    {
        return $this->goodsRepo->getGoodsConstraint($typeId, $constraintType);
    }
}
