<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/9/14
 * Time: 12:25
 */

namespace App\Exceptions\Goods;

/**
 * Class GoodsExceptionCode
 * 商品异常代码
 *
 * @package App\Exceptions\Goods
 */
final class GoodsExceptionCode
{
    /**
     * 商品不存在或已删除
     */
    const GOODS_NOT = 101;

    /**
     * 父级分类不存在
     */
    const GOODS_PARENT_TYPE_NOT = 102;

    /**
     * 分类不存在
     */
    const GOODS_TYPE_NOT = 103;

    /**
     * 分类下已存在商品
     */
    const GOODS_EXIST = 104;
}