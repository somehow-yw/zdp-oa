<?php
/**
 * Created by PhpStorm.
 * 商品分类数据中心.
 * User: fer
 * Date: 2016/9/26
 * Time: 15:36
 */

namespace App\Repositories\Goods;

use DB;

use App\Repositories\Goods\Contracts\GoodsTypeRepository as RepositoriesContract;

use App\Models\DpGoodsType;

use App\Exceptions\AppException;
use App\Exceptions\Goods\GoodsExceptionCode;

class GoodsTypeRepository implements RepositoriesContract
{
    /**
     * 商品分类添加
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::addGoodsType()
     *
     * @param int    $areaId   大区ID
     * @param int    $parentId 父分类ID
     * @param string $typeName 分类名称
     * @param string $keywords 分类关键词
     * @param string $picUrl   分类小图标地址
     *
     * @return object
     * @throws AppException
     */
    public function addGoodsType($areaId, $parentId, $typeName, $keywords, $picUrl)
    {
        // 取得所有祖先ID串
        $nodeIds = 0;
        $series = 0;
        if ($parentId > 0) {
            $goodsTypeInfoObj = $this->getGoodsTypeInfoById($parentId);
            if (!$goodsTypeInfoObj) {
                throw new AppException('父级分类不存在', GoodsExceptionCode::GOODS_PARENT_TYPE_NOT);
            }
            $nodeIds = $goodsTypeInfoObj->nodeid;
            $series = count(explode(',', $nodeIds));
        }
        $series++;

        // 添加分类
        $addArr = [
            'sort_name' => $typeName,
            'area_id'   => $areaId,
            'fid'       => $parentId,
            'nodeid'    => $nodeIds,
            'keywords'  => $keywords,
            'series'    => $series,
            'pic_url'   => $picUrl,
        ];
        $newGoodsTypeObj = null;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($addArr, $nodeIds, &$newGoodsTypeObj) {
                $newGoodsTypeObj = DpGoodsType::create($addArr);
                // 修改新添加的分类祖先ID，加进自身的ID
                $nodeIds = empty($nodeIds) ? $newGoodsTypeObj->id : $nodeIds . ',' . $newGoodsTypeObj->id;
                $newGoodsTypeObj->nodeid = $nodeIds;
                $newGoodsTypeObj->save();
            }
        );

        return $newGoodsTypeObj;
    }

    /**
     * 商品分类列表
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::getGoodsTypeList()
     *
     * @param int $areaId 大区ID
     *
     * @return \App\Models\DpGoodsType Eloquent
     */
    public function getGoodsTypeList($areaId)
    {
        $goodsTypeObjs = DpGoodsType::where('area_id', $areaId)
            ->select(['id', 'sort_name', 'fid', 'nodeid', 'goods_number', 'sort_value', 'pic_url'])
            ->orderBy('series', 'asc')
            ->orderBy('sort_value', 'asc')
            ->get();

        return $goodsTypeObjs;
    }

    /**
     * 商品分类修改
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::updateGoodsType()
     *
     * @param int    $typeId   分类ID
     * @param string $typeName 分类名称
     * @param string $keywords 分类关键词
     * @param string $picUrl   分类小图标地址
     *
     * @return object
     * @throws AppException
     */
    public function updateGoodsType($typeId, $typeName, $keywords, $picUrl)
    {
        $goodsTypeInfoObj = $this->getGoodsTypeInfoById($typeId);
        if (!$goodsTypeInfoObj) {
            throw new AppException('分类不存在', GoodsExceptionCode::GOODS_TYPE_NOT);
        }

        $goodsTypeInfoObj->sort_name = $typeName;
        $goodsTypeInfoObj->keywords = $keywords;
        $goodsTypeInfoObj->pic_url = $picUrl;
        $goodsTypeInfoObj->save();

        return $goodsTypeInfoObj;
    }

    /**
     * 商品分类删除
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::delGoodsType()
     *
     * @param array $typeIdArr 分类ID 格式：[1,2,3]
     *
     * @return void
     */
    public function delGoodsType(array $typeIdArr)
    {
        DpGoodsType::whereIn('id', $typeIdArr)
            ->delete();
    }

    /**
     * 根据NODEID串取得当前分类及所有下属分类的信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::getGoodsTypesChildByNodeIdLike()
     *
     * @param string $nodeIds 所有父节点ID串
     *
     * @return \App\Models\DpGoodsType Eloquent
     */
    public function getGoodsTypesChildByNodeIdLike($nodeIds)
    {
        $goodsTypeObjs = DpGoodsType::where('nodeid', 'like', "{$nodeIds}%")
            ->select(['id'])
            ->get();

        return $goodsTypeObjs;
    }

    /**
     * 根据ID取得分类信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::getGoodsTypeInfoById()
     *
     * @param int $id 分类ID
     *
     * @return object|null
     */
    public function getGoodsTypeInfoById($id)
    {
        return DpGoodsType::where('id', $id)
            ->select(['id', 'sort_name', 'fid', 'nodeid', 'keywords', 'pic_url'])
            ->first();
    }

    /**
     * 更改商品分类下的商品数量
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::updateGoodsTypeGoodsNumber()
     *
     * @param $goodsTypeId int 分类ID
     * @param $goodsNum    int 修改的数量 正数为增加 负数为减少
     *
     * @return void
     */
    public function updateGoodsTypeGoodsNumber($goodsTypeId, $goodsNum = 0)
    {
        $goodsTypeObj = DpGoodsType::where('id', $goodsTypeId)
            ->select('nodeid')
            ->first();
        if (!is_null($goodsTypeObj)) {
            $nodeIdArr = explode(',', $goodsTypeObj->nodeid);
            if ($goodsNum > 0) {
                DpGoodsType::whereIn('id', $nodeIdArr)
                    ->increment('goods_number', $goodsNum);
            } elseif ($goodsNum < 0) {
                $goodsNum = abs($goodsNum);
                DpGoodsType::whereIn('id', $nodeIdArr)
                    ->decrement('goods_number', $goodsNum);
            }
        }
    }

    /**
     * 商品分类排序操作
     *
     * @see \App\Repositories\Goods\Contracts\GoodsTypeRepository::sortGoodsType()
     *
     * @param array $typeSortArr array 商品分类排序数组 格式如下：
     *
     *                           [
     *                              ["type_id"=>3,"sort_value"=>2],[...]
     *                           ]
     */
    public function sortGoodsType(array $typeSortArr)
    {
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($typeSortArr) {
                foreach ($typeSortArr as $sortArr) {
                    DpGoodsType::where('id', $sortArr['type_id'])
                        ->update(['sort_value' => $sortArr['sort_value']]);
                }
            }
        );
    }
}
