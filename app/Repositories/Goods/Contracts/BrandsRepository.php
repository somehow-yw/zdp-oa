<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/26/16
 * Time: 11:16 AM
 */

namespace App\Repositories\Goods\Contracts;


use App\Models\DpBrands;

interface BrandsRepository
{
    /**
     * @param  $size  integer 每页大小
     * @param  $brand string|null 品牌名
     */
    public function getBrandsList($size, $brand = null);

    /**
     * @param $brand      string 品牌名
     * @param $key_words  string  品牌关键字
     *
     * @return void　
     */
    public function createBrand($brand, $key_words);

    /**
     * @param $id         integer 品牌id
     * @param $brand      string  品牌关键字
     * @param $key_words  string 关键字
     *
     * @return void
     */
    public function updateBrand($id, $brand, $key_words);

    /**
     * @param $id integer 品牌id
     *
     * @return void
     */
    public function deleteBrand($id);

    /**
     * 更改当前品牌下的所有商品为修改待审核
     *
     * @param $brandId int 品牌ID
     *
     * @return int 成功更改的商品数量
     */
    public function updateGoodsStatusToNotAudit($brandId);

    /**
     * @param $id integer 品牌id
     *
     * @return DpBrands;
     */
    public function findBrandById($id);

    /**
     * @param $brand string 品牌名
     *
     * @return boolean
     */
    public function hasBrand($brand);

    /**
     * @param $brand string 品牌名
     *
     * @return DpBrands
     */
    public function findBrandByName($brand);
}