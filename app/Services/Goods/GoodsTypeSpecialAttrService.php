<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/9/29
 * Time: 9:28
 */

namespace App\Services\Goods;

use App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository;

use App\Exceptions\Goods\SpecialAttrException;

class GoodsTypeSpecialAttrService
{
    private $specialAttrRepo;

    public function __construct(GoodsTypeSpecialAttrRepository $specialAttrRepo)
    {
        $this->specialAttrRepo = $specialAttrRepo;
    }

    /**
     * 商品分类特殊属性添加/修改
     *
     * @param int   $typeId     商品分类ID
     * @param array $attributes 特殊属性信息
     *                          0=>[
     *                          - attribute_id int 属性记录ID
     *                          - attribute_name string 属性名称
     *                          - format_type_id int 属性格式类型ID
     *                          - must bool 是否必填属性
     *                          - format_values=>[
     *                          -- 0=>[
     *                          --- value mixed 属性值 没有为空
     *                          --- unit string 值的单位 没有为空
     *                          --- default 当有多个值时，是否默认选中 对单选或多选一类的属性格式生效
     *                          --- rule string 值的验证类型 如：integer
     *                          -- ],[...]
     *                          - ]
     *                          ],[...]
     *
     * @return void
     * @throws SpecialAttrException
     */
    public function updateGoodsTypeSpecialAttr($typeId, $attributes)
    {
        $inputFormatArr = config('input_format.goods_attribute');
        $inputFormatCollect = collect($inputFormatArr);
        $inputFormatArr = $inputFormatCollect->keyBy('id')->toArray();
        foreach ($attributes as $attrValues) {
            if (!array_has($inputFormatArr, $attrValues['format_type_id'])) {
                throw new SpecialAttrException('属性可输入格式类型不存在', SpecialAttrException::ATTR_INPUT_FORMAT_NOT);
            }
            $formatValueRuleArr = array_pluck($attrValues['format_values'], 'rule');
            if (!empty($attrValues['attribute_id'])) {
                // 修改属性
                $this->specialAttrRepo->updateGoodsTypeSpecialAttr($attrValues, $formatValueRuleArr);
            } else {
                // 添加属性
                $this->specialAttrRepo->addGoodsTypeSpecialAttr($typeId, $attrValues, $formatValueRuleArr);
            }
        }
    }

    /**
     * 商品分类特殊属性信息列表
     *
     * @param int $goodsTypeId 商品分类ID
     *
     * @return array
     */
    public function getGoodsTypeSpecialAttrList($goodsTypeId)
    {
        $attrObjs = $this->specialAttrRepo->getGoodsTypeSpecialAttrList($goodsTypeId);
        $reDataArr = [
            'type_id'    => $goodsTypeId,
            'attributes' => [],
        ];
        if (!$attrObjs->isEmpty()) {
            $attrArr = [];
            foreach ($attrObjs as $item) {
                $attrArr[] = [
                    'attribute_id'   => $item->id,
                    'attribute_name' => $item->attribute_name,
                    'format_type_id' => $item->format_type_id,
                    'must'           => $item->must,
                    'format_values'  => json_decode($item->format_values, true),
                ];
            }
            $reDataArr['attributes'] = $attrArr;
        }

        return $reDataArr;
    }

    /**
     * 商品分类特殊属性删除.
     *
     * @param string $attributeIds 属性ID串 格式:1,2,3
     *
     * @return void
     */
    public function delGoodsTypeSpecialAttr($attributeIds)
    {
        $attributeIdArr = explode(',', $attributeIds);
        $this->specialAttrRepo->delGoodsTypeSpecialAttr($attributeIdArr);
    }

    /**
     * 取得属性对应的商品分类ID
     *
     * @param array $goodsTypeIdArr array 属性ID 格式：[1,2,3]
     *
     * @return int
     */
    public function getGoodsTypeIdById(array $goodsTypeIdArr)
    {
        return $this->specialAttrRepo->getGoodsTypeIdById($goodsTypeIdArr);
    }
}
