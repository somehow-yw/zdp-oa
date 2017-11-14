<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/15/16
 * Time: 12:01 PM
 */

namespace App\Exceptions\Goods;

class GoodsSpecialAttributesException extends AddGoodsException
{
    /**
     * 商品特殊属性参数无效(validator层)
     */
    const GOODS_SPECIAL_ATTRIBUTES_INVALID = 101;

    /**
     * 商品必填特殊属性不能为空
     */
    const GOODS_MUST_SPECIAL_ATTRIBUTES_CAN_NOT_BE_BLANK = 102;

    /**
     * 商品特殊属性名称不符合
     */
    const GOODS_SPECIAL_ATTRIBUTES_NAME_NOT_MATCH = 103;
}