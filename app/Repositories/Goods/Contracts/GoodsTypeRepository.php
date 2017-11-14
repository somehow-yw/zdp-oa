<?php
/**
 * Created by PhpStorm.
 * 商品分类数据中心.
 * User: fer
 * Date: 2016/9/26
 * Time: 15:35
 */

namespace App\Repositories\Goods\Contracts;

/**
 * Interface GoodsTypeRepository.
 * 商品分类数据处理
 * @package App\Repositories\Goods\Contracts
 */
interface GoodsTypeRepository
{
    /**
     * 商品分类添加
     *
     * @param int    $areaId   大区ID
     * @param int    $parentId 父分类ID
     * @param string $typeName 分类名称
     * @param string $keywords 分类关键词
     * @param string $picUrl   分类小图标地址
     *
     * @return object
     */
    public function addGoodsType($areaId, $parentId, $typeName, $keywords, $picUrl);

    /**
     * 商品分类列表
     *
     * @param int $areaId 大区ID
     *
     * @return  \App\Models\DpGoodsType Eloquent
     */
    public function getGoodsTypeList($areaId);

    /**
     * 商品分类修改
     *
     * @param int    $typeId   分类ID
     * @param string $typeName 分类名称
     * @param string $keywords 分类关键词
     * @param string $picUrl   分类小图标地址
     *
     * @return object
     */
    public function updateGoodsType($typeId, $typeName, $keywords, $picUrl);

    /**
     * 商品分类删除
     *
     * @param array $typeIdArr 分类ID 格式：[1,2,3]
     *
     * @return void
     */
    public function delGoodsType(array $typeIdArr);

    /**
     * 根据NODEID串取得当前分类及所有下属分类的信息
     *
     * @param string $nodeIds 所有父节点ID串
     *
     * @return \App\Models\DpGoodsType Eloquent
     */
    public function getGoodsTypesChildByNodeIdLike($nodeIds);

    /**
     * 根据ID取得分类信息
     *
     * @param int $id 分类ID
     *
     * @return object|null
     */
    public function getGoodsTypeInfoById($id);

    /**
     * 更改商品分类下的商品数量
     *
     * @param $goodsTypeId int 分类ID
     * @param $goodsNum    int 修改的数量 正数为增加 负数为减少
     *
     * @return void
     */
    public function updateGoodsTypeGoodsNumber($goodsTypeId, $goodsNum = 0);

    /**
     * 商品分类排序操作
     *
     * @param array $typeSortArr array 商品分类排序数组 格式如下：
     *
     *                           [
     *                              ["type_id"=>3,"sort_value"=>2],[...]
     *                           ]
     */
    public function sortGoodsType(array $typeSortArr);
}
