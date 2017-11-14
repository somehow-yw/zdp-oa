<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/6
 * Time: 11:52
 */

namespace app\Repositories\Goods;

use App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository as RepositoriesContract;

use App\Models\DpGoodsTypeSpecialAttribute;

use App\Exceptions\Goods\SpecialAttrException;

/**
 * Class GoodsTypeSpecialAttrRepository.
 * 商品分类特殊属性
 * @package app\Repositories\Goods
 */
class GoodsTypeSpecialAttrRepository implements RepositoriesContract
{
    /**
     * 修改商品分类的特殊属性
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::updateGoodsTypeSpecialAttr()
     *
     * @param array $attrValues         属性内容
     *                                  [
     *                                  - attribute_id int 属性记录ID
     *                                  - attribute_name string 属性名称
     *                                  - format_type_id int 属性格式类型ID
     *                                  - must bool 是否必填属性
     *                                  - format_values=>[
     *                                  -- 0=>[
     *                                  --- value mixed 属性值 没有为空
     *                                  --- unit string 值的单位 没有为空
     *                                  --- default 当有多个值时，是否默认选中 对单选或多选一类的属性格式生效
     *                                  --- rule string 值的验证类型 如：integer
     *                                  -- ],[...]
     *                                  - ]
     * @param array $formatValueRuleArr 属性值的验证方式 格式：['string', 'integer', ...]
     *
     * @return mixed
     * @throws SpecialAttrException
     */
    public function updateGoodsTypeSpecialAttr($attrValues, $formatValueRuleArr)
    {
        $attrObj = $this->getAttrById($attrValues['attribute_id']);
        if (is_null($attrObj)) {
            throw new SpecialAttrException('属性不存在，修改失败', SpecialAttrException::ATTR_NOT);
        }
        $attrObj->attribute_name = $attrValues['attribute_name'];
        $attrObj->format_type_id = $attrValues['format_type_id'];
        $attrObj->must = $attrValues['must'] ? 1 : 0;
        $attrObj->format_values = json_encode($attrValues['format_values']);
        $attrObj->format_rules = json_encode($formatValueRuleArr);
        $attrObj->save();

        return $attrObj;
    }

    /**
     * 添加商品分类的特殊属性
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::addGoodsTypeSpecialAttr()
     *
     * @param int   $typeId             商品分类ID
     * @param array $attrValues         属性内容
     *                                  [
     *                                  - attribute_id int 属性记录ID
     *                                  - attribute_name string 属性名称
     *                                  - format_type_id int 属性格式类型ID
     *                                  - must bool 是否必填属性
     *                                  - format_values=>[
     *                                  -- 0=>[
     *                                  --- value mixed 属性值 没有为空
     *                                  --- unit string 值的单位 没有为空
     *                                  --- default 当有多个值时，是否默认选中 对单选或多选一类的属性格式生效
     *                                  --- rule string 值的验证类型 如：integer
     *                                  -- ],[...]
     *                                  - ]
     * @param array $formatValueRuleArr 属性值的验证方式 格式：['string', 'integer', ...]
     *
     * @return mixed
     * @throws SpecialAttrException
     */
    public function addGoodsTypeSpecialAttr($typeId, $attrValues, $formatValueRuleArr)
    {
        $attrObj = $this->getAttrByGoodsTypeAndName($typeId, $attrValues['attribute_name']);
        if (!is_null($attrObj)) {
            throw new SpecialAttrException('属性已存在，不可重复添加', SpecialAttrException::ATTR_EXISTING);
        }
        $addArr = [
            'type_id'        => $typeId,
            'attribute_name' => $attrValues['attribute_name'],
            'format_type_id' => $attrValues['format_type_id'],
            'must'           => $attrValues['must'],
            'format_values'  => json_encode($attrValues['format_values']),
            'format_rules'   => json_encode($formatValueRuleArr),
        ];

        return DpGoodsTypeSpecialAttribute::create($addArr);
    }

    /**
     * 商品分类特殊属性列表
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::getGoodsTypeSpecialAttrList()
     *
     * @param int $goodsTypeId 商品分类ID
     *
     * @return  \App\Models\DpGoodsTypeSpecialAttribute Eloquent collect
     */
    public function getGoodsTypeSpecialAttrList($goodsTypeId)
    {
        return DpGoodsTypeSpecialAttribute::where('type_id', $goodsTypeId)
            ->select(['id', 'attribute_name', 'format_type_id', 'must', 'format_values', 'format_rules'])
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * 商品分类特殊属性删除
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::delGoodsTypeSpecialAttr()
     *
     * @param array $attributeIdArr 分类属性ID 格式：[1,2,3]
     *
     * @return void
     */
    public function delGoodsTypeSpecialAttr(array $attributeIdArr)
    {
        DpGoodsTypeSpecialAttribute::whereIn('id', $attributeIdArr)
            ->delete();
    }

    /**
     * 根据记录ID获得属性约束信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::getConstraintInfoById()
     *
     * @param       $id              int 分类属性ID
     * @param array $columnSelectArr array 列选择
     *
     * @return object|null
     */
    public function getConstraintInfoById($id, array $columnSelectArr = ['*'])
    {
        return DpGoodsTypeSpecialAttribute::where('id', $id)
            ->select($columnSelectArr)
            ->first();
    }

    /**
     * 取得属性对应的商品分类ID
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository::getGoodsTypeIdById()
     *
     * @param array $goodsTypeIdArr array 属性ID 格式：[1,2,3]
     *
     * @return int
     */
    public function getGoodsTypeIdById(array $goodsTypeIdArr)
    {
        $goodsTypeInfo = DpGoodsTypeSpecialAttribute::whereIn('id', $goodsTypeIdArr)
            ->select('type_id')
            ->first();

        return is_null($goodsTypeInfo) ? 0 : $goodsTypeInfo->type_id;
    }

    /**
     * 根据ID查询记录信息
     *
     * @param $attrId
     *
     * @return object|null
     */
    private function getAttrById($attrId)
    {
        return DpGoodsTypeSpecialAttribute::where('id', $attrId)
            ->first();
    }

    /**
     * 根据商品分类及属性名称查询信息
     *
     * @param int    $goodsTypeId 商品分类ID
     * @param string $attrName    属性名称
     *
     * @return object|null
     */
    private function getAttrByGoodsTypeAndName($goodsTypeId, $attrName)
    {
        return DpGoodsTypeSpecialAttribute::where('type_id', $goodsTypeId)
            ->where('attribute_name', $attrName)
            ->first();
    }
}
