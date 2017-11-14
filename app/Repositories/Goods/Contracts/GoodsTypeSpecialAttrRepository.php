<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/6
 * Time: 11:52
 */

namespace App\Repositories\Goods\Contracts;

/**
 * Interface GoodsTypeSpecialAttrRepository.
 * 商品分类特殊属性处理
 * @package App\Repositories\Goods\Contracts
 */
interface GoodsTypeSpecialAttrRepository
{
    /**
     * 修改商品分类的特殊属性
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
     */
    public function updateGoodsTypeSpecialAttr($attrValues, $formatValueRuleArr);

    /**
     * 添加商品分类特殊属性
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
     */
    public function addGoodsTypeSpecialAttr($typeId, $attrValues, $formatValueRuleArr);

    /**
     * 商品分类特殊属性列表
     *
     * @param int $goodsTypeId 商品分类ID
     *
     * @return \App\Models\DpGoodsTypeSpecialAttribute Eloquent collect
     */
    public function getGoodsTypeSpecialAttrList($goodsTypeId);

    /**
     * 商品分类特殊属性删除
     *
     * @param array $attributeIdArr 分类属性ID 格式：[1,2,3]
     *
     * @return void
     */
    public function delGoodsTypeSpecialAttr(array $attributeIdArr);

    /**
     * 根据记录ID获得属性约束信息
     *
     * @param       $id              int 分类属性ID
     * @param array $columnSelectArr array 列选择
     *
     * @return object|null
     */
    public function getConstraintInfoById($id, array $columnSelectArr = ['*']);

    /**
     * 取得属性对应的商品分类ID
     *
     * @param array $goodsTypeIdArr array 属性ID 格式：[1,2,3]
     *
     * @return int
     */
    public function getGoodsTypeIdById(array $goodsTypeIdArr);
}
