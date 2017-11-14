<?php
/**
 * Created by PhpStorm.
 * 商品分类处理.
 *
 * User: fer
 * Date: 2016/9/26
 * Time: 15:33
 */

namespace App\Services\Goods;

use DB;
use App;

use App\Repositories\Goods\Contracts\GoodsTypeRepository;
use App\Repositories\Goods\Contracts\GoodsRepository;

use App\Exceptions\AppException;
use App\Exceptions\Goods\GoodsExceptionCode;

/**
 * Class GoodsTypeService.
 * 商品分类操作
 * @package App\Services\Goods
 */
class GoodsTypeService
{
    private $goodsTypeRepo;

    public function __construct(GoodsTypeRepository $goodsTypeRepo)
    {
        $this->goodsTypeRepo = $goodsTypeRepo;
    }

    /**
     * 商品分类添加
     *
     * @param int    $areaId   大区ID
     * @param int    $parentId 父分类ID
     * @param string $typeName 分类名称
     * @param string $keywords 分类关键词
     * @param string $picUrl   分类小图标地址
     *
     * @return array
     */
    public function addGoodsType($areaId, $parentId, $typeName, $keywords, $picUrl)
    {
        $keywords = str_replace('，', ',', $keywords);
        $newGoodsTypeObj = $this->goodsTypeRepo->addGoodsType($areaId, $parentId, $typeName, $keywords, $picUrl);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 商品分类列表
     *
     * @param int $areaId 大区ID
     *
     * @return array
     */
    public function getGoodsTypeList($areaId)
    {
        $goodsTypeObjs = $this->goodsTypeRepo->getGoodsTypeList($areaId);

        $reGoodsTypeArrs = [];
        if (!$goodsTypeObjs->isEmpty()) {
            foreach ($goodsTypeObjs as $item) {
                $nodeIds = $item->nodeid;
                $keys = str_replace(',', '.', $nodeIds);
                $varTempArr = [
                    'type_id'      => $item->id,
                    'type_name'    => $item->sort_name,
                    'goods_number' => $item->goods_number,
                    'sort_value'   => $item->sort_value,
                    'type_pic_url' => $item->pic_url,
                ];
                array_set($reGoodsTypeArrs, $keys, $varTempArr);
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reGoodsTypeArrs,
        ];
    }

    /**
     * 商品分类详情
     *
     * @param $typeId
     *
     * @return array
     * @throws AppException
     */
    public function getGoodsTypeInfo($typeId)
    {
        $goodsTypeObj = $this->goodsTypeRepo->getGoodsTypeInfoById($typeId);
        if (!$goodsTypeObj) {
            throw new AppException('分类不存在', GoodsExceptionCode::GOODS_TYPE_NOT);
        }
        $reDataArr = [
            'type_id'       => $goodsTypeObj->id,
            'type_name'     => $goodsTypeObj->sort_name,
            'type_keywords' => $goodsTypeObj->keywords,
            'type_pic_url'  => $goodsTypeObj->pic_url,
        ];

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 商品分类修改
     *
     * @param int    $typeId   分类ID
     * @param string $typeName 分类名称
     * @param string $keywords 分类关键词
     * @param string $picUrl   分类小图标地址
     *
     * @return array
     */
    public function updateGoodsType($typeId, $typeName, $keywords, $picUrl)
    {
        $keywords = str_replace('，', ',', $keywords);
        $goodsTypeObjs = $this->goodsTypeRepo->updateGoodsType($typeId, $typeName, $keywords, $picUrl);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 商品分类删除
     *
     * @param int $typeId 分类ID
     *
     * @return array
     */
    public function delGoodsType($typeId)
    {
        $goodsTypeInfoObj = $this->goodsTypeRepo->getGoodsTypeInfoById($typeId);
        if ($goodsTypeInfoObj) {
            // 查询出当前分类下所有子类（包括当前分类）
            $goodsTypeObjs = $this->goodsTypeRepo->getGoodsTypesChildByNodeIdLike($goodsTypeInfoObj->nodeid);
            if (!$goodsTypeObjs->isEmpty()) {
                $self = $this;
                DB::connection('mysql_zdp_main')->transaction(
                    function () use ($self, $goodsTypeObjs) {
                        // 处理分类ID
                        $goodsTypeObj = $goodsTypeObjs->pluck('id');
                        $goodsTypeArr = $goodsTypeObj->all();
                        // 查询是否存在商品
                        /** @var $goodsRepo GoodsRepository */
                        $goodsRepo = App::make(GoodsRepository::class);
                        $goodsNum = $goodsRepo->getGoodsNumByTypeIds($goodsTypeArr);
                        if ($goodsNum) {
                            throw new AppException('分类下已存在商品，不可删除', GoodsExceptionCode::GOODS_EXIST);
                        }
                        // 删除分类
                        $self->goodsTypeRepo->delGoodsType($goodsTypeArr);
                    }
                );
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 商品分类排序操作
     *
     * @param array $typeSortArr array 商品分类排序数组 格式如下：
     *
     *                           [
     *                              ["type_id"=>3,"sort_value"=>2],[...]
     *                           ]
     */
    public function sortGoodsType(array $typeSortArr)
    {
        $this->goodsTypeRepo->sortGoodsType($typeSortArr);
    }
}
