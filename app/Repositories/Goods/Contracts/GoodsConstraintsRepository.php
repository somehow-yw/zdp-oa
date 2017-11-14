<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/27/16
 * Time: 6:32 PM
 */

namespace App\Repositories\Goods\Contracts;

use App\Models\DpGoodsConstraints;

interface GoodsConstraintsRepository
{
    /**
     * 获取$type_id对应分类的基本属性
     *
     * @param $type_id
     *
     * @return array
     */
    public function getGoodsBasicAttr($type_id);

    /**
     * 更新$type_id对应分类的基本属性
     *
     * @param $typeId         integer 商品分类id
     * @param $constraintType integer 0-类型约束 1-规格约束
     * @param $formatTypeId   integer 格式类型id 详见config/input_format.php
     * @param $formatValues   array 格式约束值
     *
     * @return void
     */
    public function updateGoodsBasicAttr($typeId, $constraintType, $formatTypeId, $formatValues);

    /**
     * 获取$type_id 对应分类的$constraintType约束
     *
     * @param $type_id
     * @param $constraintType integer 商品约束类型
     *                        规格约束
     *                        DpGoodsConstraints::SPEC_CONSTRAINT
     *                        or
     *                        类型约束
     *                        DpGoodsConstraints::TYPE_CONSTRAINT
     *
     * @return DpGoodsConstraints|null
     */
    public function getGoodsConstraint($type_id, $constraintType);

    /**
     * 获取$type_id 对应分类的规格约束
     *
     * @param $type_id
     *
     * @return DpGoodsConstraints|null
     */
    public function getGoodsSpecConstraint($type_id);

    /**
     * 获取$type_id 对应分类的类型约束
     *
     * @param $type_id
     *
     * @return DpGoodsConstraints|null
     */
    public function getGoodsTypeConstraint($type_id);

    /**
     * 获取对应记录的信息
     *
     * @param       $id              int 记录ID
     * @param array $columnSelectArr array 列选择
     *
     * @return object|null
     */
    public function getConstraintById($id, array $columnSelectArr = ['*']);
}