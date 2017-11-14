<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/11/2
 * Time: 9:50
 */

namespace App\Workflows;

use DB;

use App\Services\Goods\GoodsTypeSpecialAttrService;
use App\Services\Goods\GoodsOperationService;

use App\Exceptions\Goods\SpecialAttrException;

class GoodsTypeSpecialAttrWorkflow
{
    private $goodsTypeSpecialAttrService;
    private $goodsOperationService;

    public function __construct(
        GoodsTypeSpecialAttrService $goodsTypeSpecialAttrService,
        GoodsOperationService $goodsOperationService
    ) {
        $this->goodsTypeSpecialAttrService = $goodsTypeSpecialAttrService;
        $this->goodsOperationService = $goodsOperationService;
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
     * @return array
     */
    public function updateGoodsTypeSpecialAttr($typeId, $attributes)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $typeId, $attributes) {
                // 添加/修改
                $self->goodsTypeSpecialAttrService->updateGoodsTypeSpecialAttr($typeId, $attributes);
                // 更改此属性对应分类下的商品状态为待审核
                $self->goodsOperationService->updateGoodsStatusToNotAudit($typeId);
            }
        );

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 商品分类特殊属性删除.
     *
     * @param string $attributeIds 属性ID串 格式:1,2,3
     *
     * @return array
     * @throws SpecialAttrException
     */
    public function delGoodsTypeSpecialAttr($attributeIds)
    {
        // 取得此次删除的属性对应的商品分类ID
        $attributeIdArr = explode(',', $attributeIds);
        $goodsTypeId = $this->goodsTypeSpecialAttrService->getGoodsTypeIdById($attributeIdArr);
        if (empty($goodsTypeId)) {
            throw new SpecialAttrException('属性约束不存在', SpecialAttrException::ATTR_NOT);
        }
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $attributeIds, $goodsTypeId) {
                // 删除
                $self->goodsTypeSpecialAttrService->delGoodsTypeSpecialAttr($attributeIds);
                // 更改此属性对应分类下的商品状态为待审核
                $self->goodsOperationService->updateGoodsStatusToNotAudit($goodsTypeId);
            }
        );

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }
}
