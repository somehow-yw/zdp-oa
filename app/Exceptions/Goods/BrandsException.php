<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/26/16
 * Time: 11:58 AM
 */

namespace App\Exceptions\Goods;


final class BrandsException
{
    const BRANDS_NOT_FOUND_CODE = 101;
    const BRAND_NOT_FOUND_MSG = "该品牌不存在";
    const BRAND_ALREADY_EXIST_CODE = 102;
    const BRAND_ALREADY_EXIST_MSG = "该品牌已存在";
}