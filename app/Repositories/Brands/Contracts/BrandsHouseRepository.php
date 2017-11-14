<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-2-10
 * Time: 下午1:44
 */

namespace App\Repositories\Brands\Contracts;

interface BrandsHouseRepository
{
    /**
     * 获取品牌馆列表
     *
     * @param $areaId integer 大区ID
     *
     * @return Collection
     */
    public function getBrandsList($areaId);

    /**
     * 添加品牌到品牌馆
     *
     * @param $areaId      integer 大区ID
     * @param $brandId      integer 品牌ID
     * @param $putOnAt     string 上架展示时间
     * @param $pullOffAt   string 下架时间
     * @param $position    string 位置
     * @param $image       string 图片
     *
     * @return void
     */
    public function addBrands($areaId, $brandId, $putOnAt, $pullOffAt, $position, $image);
}
