<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/13/16
 * Time: 11:54 AM
 */

namespace App\Repositories\Shops\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface HighQualitySupplierRepository
{
    /**
     * 获取优质供应商列表
     *
     * @param $areaId integer 大区ID
     *
     * @return Collection
     */
    public function getSuppliersList($areaId);

    /**
     * 添加优质供应商
     *
     * @param $areaId      integer 大区ID
     * @param $shopId      integer 店铺ID
     * @param $putOnAt     string 上架展示时间
     * @param $pullOffAt   string 下架时间
     * @param $position    string 位置
     * @param $image       string 图片
     *
     * @return void
     */
    public function addSupplier($areaId, $shopId, $putOnAt, $pullOffAt, $position, $image);

    /**
     * 下架优质供应商
     *
     * @param $id  integer 优质供应商记录自增id
     *
     * @return mixed
     */
    public function pullOffSupplier($id);
}