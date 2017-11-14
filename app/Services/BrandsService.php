<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/26/16
 * Time: 10:48 AM
 */

namespace App\Services;

use DB;

use App\Exceptions\AppException;
use App\Exceptions\Goods\BrandsException;
use App\Repositories\Goods\Contracts\BrandsRepository;

class BrandsService
{
    protected $repository;

    public function __construct(BrandsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取品牌列表
     *
     * @param $size  integer 每页大小
     * @param $page  integer 当前页数
     * @param $brand string|null 品牌名
     *
     * @return array
     */
    public function getBrandsList($size, $page, $brand = null)
    {
        $brands = $this->repository->getBrandsList($size, $brand);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'page'   => (int)$page,
                'total'  => $brands->total(),
                'brands' => $brands->toArray()['data'],
            ],
        ];
    }

    /**
     * 删除品牌
     *
     * @param $id integer 品牌id
     */
    public function deleteBrand($id)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $id) {
                $self->repository->deleteBrand($id);
                $self->repository->updateGoodsStatusToNotAudit($id);
            }
        );
    }

    /**
     * 更新品牌
     *
     * @param $id        integer 品牌id
     * @param $brand     string 品牌名
     * @param $key_words string 关键字　
     */
    public function updateBrand($id, $brand, $key_words)
    {
        $key_words = $this->replaceChineseCommaToEnglishComma($key_words);
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $id, $brand, $key_words) {
                $this->repository->updateBrand($id, $brand, $key_words);
                $self->repository->updateGoodsStatusToNotAudit($id);
            }
        );
    }

    /**
     * 添加品牌
     *
     * @param $brand     string 品牌名
     * @param $key_words string 品牌名
     *
     * @throws AppException 品牌已经存在
     */
    public function createBrand($brand, $key_words)
    {
        $key_words = $this->replaceChineseCommaToEnglishComma($key_words);
        if ($this->repository->hasBrand($brand)) {
            $dp_brand = $this->repository->findBrandByName($brand);
            // 如果该品牌已经被软删除 恢复该品牌并更新key_words字段
            if ($dp_brand->trashed()) {
                $dp_brand->key_words = $key_words;
                $dp_brand->restore();
            } else {
                throw  new AppException(
                    BrandsException::BRAND_ALREADY_EXIST_MSG,
                    BrandsException::BRAND_ALREADY_EXIST_CODE
                );
            }
        } else {
            $this->repository->createBrand($brand, $key_words);
        }
    }

    /**
     * 搜索品牌
     *
     * @param $brand string 品牌名
     * @param $page  integer 当前页数
     * @param $size  integer 每页大小
     *
     * @return array
     */
    public function searchBrand($brand, $page, $size)
    {
        $brands = $this->repository->searchBrandsByName($brand, $size);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'page'   => (int)$page,
                'total'  => $brands->count(),
                'brands' => $brands->toArray()['data'],
            ],
        ];
    }

    /**
     * 将中文逗号替换成英文
     *
     * @param $content string 源字符串
     *
     * @return string
     */
    protected function replaceChineseCommaToEnglishComma($content)
    {
        return str_replace("，", ",", $content);
    }
}
