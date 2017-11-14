<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/26/16
 * Time: 11:58 AM
 */

namespace App\Exceptions\Goods;

use App\Exceptions\AppException;

class GoodsSpecConstraintsException extends AppException
{
    /**
     * 商品类型约束值组数与输入类型不匹配
     */
    const GOODS_CONSTRAINT_FORMAT_VALUES_NOT_MATCHED = 101;
    /**
     * 商品类型约束格式缺少键
     */
    const GOODS_CONSTRAINT_FORMAT_VALUE_KEY_MISS = 102;
    /**
     * 添加商品参数不合法(validator层)
     */
    const ADD_GOODS_PARAMETERS_INVALID = 103;
    /**
     * 商品属性不满足类型约束
     */
    const GOODS_ATTR_NOT_MATCH_CONSTRAINT = 104;
}