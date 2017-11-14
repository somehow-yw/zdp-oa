<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/27/16
 * Time: 6:36 PM
 */

namespace App\Repositories\Goods;

use DB;
use App\Models\DpGoodsConstraints;
use App\Repositories\Goods\Contracts\GoodsConstraintsRepository as RepositoryContracts;

/**
 * Class GoodsConstraintsRepository
 * 商品分类基本属性
 * @package App\Repositories\Goods
 */
class GoodsConstraintsRepository implements RepositoryContracts
{
    /**
     * 获取商品分类基本属性
     *
     * @param $type_id
     *
     * @return array
     */
    public function getGoodsBasicAttr($type_id)
    {
        $type_constraint = $this->getGoodsTypeConstraint($type_id);
        $spec_constraint = $this->getGoodsSpecConstraint($type_id);

        if (is_null($type_constraint)) {
            $type_constraint = new \stdClass();
        } else {
            $type_constraint->format_values = json_decode($type_constraint->format_values, true);
        }
        if (is_null($spec_constraint)) {
            $spec_constraint = new \stdClass();
        } else {
            $spec_constraint->format_values = json_decode($spec_constraint->format_values, true);
        }

        return compact('type_constraint', 'spec_constraint');
    }

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
    public function updateGoodsBasicAttr($typeId, $constraintType, $formatTypeId, $formatValues)
    {
        /** @var DpGoodsConstraints $constraint */
        $constraint = DpGoodsConstraints::where('type_id', $typeId)
            ->where('constraint_type', $constraintType)
            ->first();
        //约束不存在,创建
        if (is_null($constraint)) {
            $constraint = new DpGoodsConstraints(
                [
                    'constraint_type' => $constraintType,
                    'type_id'         => $typeId,
                ]
            );
        }
        $constraint->format_type_id = $formatTypeId;
        $constraint->format_rule = json_encode($this->buildFormatRules($formatValues));
        $constraint->format_values = json_encode($formatValues);
        $constraint->save();
    }

    /**
     * @see  \App\Repositories\Goods\Contracts\GoodsConstraintsRepository::getGoodsTypeConstraint()
     */
    public function getGoodsTypeConstraint($type_id)
    {
        $type_constraint = DpGoodsConstraints::select('id as attribute_id', 'format_type_id', 'format_values')
            ->where('type_id', $type_id)
            ->where('constraint_type', DpGoodsConstraints::TYPE_CONSTRAINT)->first();

        return $type_constraint;
    }

    /**
     * @see  \App\Repositories\Goods\Contracts\GoodsConstraintsRepository::getGoodsConstraint()
     */
    public function getGoodsConstraint($type_id, $constraintType)
    {
        $constraint = DpGoodsConstraints::select('format_type_id', 'format_rule', 'format_values')
            ->where('type_id', $type_id)
            ->where('constraint_type', $constraintType)->first();

        return $constraint;
    }

    /**
     * @see  \App\Repositories\Goods\Contracts\GoodsConstraintsRepository::getGoodsSpecConstraint()
     */
    public function getGoodsSpecConstraint($type_id)
    {
        $spec_constraint = DpGoodsConstraints::select('id as attribute_id', 'format_type_id', 'format_values')
            ->where('type_id', $type_id)
            ->where('constraint_type', DpGoodsConstraints::SPEC_CONSTRAINT)->first();

        return $spec_constraint;
    }

    /**
     * 获取对应记录的信息
     *
     * @see  \App\Repositories\Goods\Contracts\GoodsConstraintsRepository::getConstraintById()
     *
     * @param       $id              int 记录ID
     * @param array $columnSelectArr array 列选择
     *
     * @return object|null
     */
    public function getConstraintById($id, array $columnSelectArr = ['*'])
    {
        return DpGoodsConstraints::where('id', $id)
            ->select($columnSelectArr)
            ->first();
    }

    /**
     * 构造商品格式约束规则数组
     *
     * @param $formatValues array 商品类型格式约束值
     *
     * @return array
     */
    protected function buildFormatRules($formatValues)
    {
        $formatRule = [];
        foreach ($formatValues as $formatValue) {
            $formatRule[] = $formatValue['rule'];
        }

        return $formatRule;
    }
}
