<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/15/16
 * Time: 12:14 PM
 */

namespace App\Exceptions\Goods;

use App\Exceptions\AppException;

class AddGoodsException extends AppException
{
    /**
     * 商品添加图片参数无效
     */
    const GOODS_PICTURES_INVALID = 101;
    /**
     * 商品检验报告无效
     */
    const GOODS_INSPECTIONS_REPORT_INVALID = 102;
}