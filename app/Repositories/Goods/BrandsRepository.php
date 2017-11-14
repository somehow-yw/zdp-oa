<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/26/16
 * Time: 11:30 AM
 */

namespace App\Repositories\Goods;

use App;
use DB;
use Zdp\Search\Services\ElasticService;

use App\Exceptions\AppException;
use App\Exceptions\Goods\BrandsException;
use App\Models\DpBrands;
use App\Models\DpGoodsInfo;

use App\Repositories\Goods\Contracts\BrandsRepository as RepositoryContracts;

class BrandsRepository implements RepositoryContracts
{
    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::getBrandsList()
     */
    public function getBrandsList($size, $brand = null)
    {
        $query = DpBrands::select('id', 'brand', 'key_words')->orderBy('updated_at', 'desc');
        if (is_null($brand)) {
            return $query->paginate($size);
        } else {
            return $query->where("brand", "like", "%" . $brand . "%")->paginate($size);
        }
    }

    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::deleteBrand()
     */
    public function deleteBrand($id)
    {
        $dp_brand = $this->findBrandById($id);
        if (empty($dp_brand)) {
            throw new AppException(BrandsException::BRAND_NOT_FOUND_MSG, BrandsException::BRANDS_NOT_FOUND_CODE);
        }
        $dp_brand->delete();
    }

    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::createBrand()
     */
    public function createBrand($brand, $key_words)
    {
        $dp_brand = new DpBrands();
        $dp_brand->brand = $brand;
        $dp_brand->key_words = $key_words;
        $dp_brand->save();
    }

    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::updateBrand()
     */
    public function updateBrand($id, $brand, $key_words)
    {
        $dp_brand = $this->findBrandById($id);
        if (empty($dp_brand)) {
            throw new AppException(BrandsException::BRAND_NOT_FOUND_MSG, BrandsException::BRANDS_NOT_FOUND_CODE);
        }
        $dp_brand->brand = $brand;
        $dp_brand->key_words = $key_words;
        $dp_brand->save();
    }

    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::findBrandById()
     */
    public function findBrandById($id)
    {
        return DpBrands::find($id);
    }

    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::hasBrand()
     */
    public function hasBrand($brand)
    {
        return DpBrands::withTrashed()->where('brand', '=', $brand)->count() > 0;
    }

    /**
     * @see \App\Repositories\Goods\Contracts\BrandsRepository::findBrandByName()
     */
    public function findBrandByName($brand)
    {
        return DpBrands::withTrashed()->where('brand', '=', $brand)->first();
    }

    /**
     * 更改当前品牌下的所有商品为修改待审核
     *
     * @param $brandId int 品牌ID
     *
     * @return int 成功更改的商品数量
     */
    public function updateGoodsStatusToNotAudit($brandId)
    {
        $updateArr = [
            'shenghe_act' => DpGoodsInfo::STATUS_MODIFY_AUDIT,
        ];

        // 获取所有影响商品的ID串
        $goodsIdsCollect = DpGoodsInfo::where('brand_id', $brandId)
            ->select(['id as goods_id'])
            ->get();
        $updateNum = DpGoodsInfo::where('brand_id', $brandId)
            ->update($updateArr);
        if (!$goodsIdsCollect->isEmpty()) {
            $goodsIdArr = $goodsIdsCollect->pluck('goods_id')->toArray();
            // 进行商品搜索索引更新
            /** @var ElasticService $elasticIndexUpdateObj */
            $elasticIndexUpdateObj = App::make(ElasticService::class);
            $elasticIndexUpdateObj->updateGoods($goodsIdArr);
        }

        return $updateNum;
    }
}
