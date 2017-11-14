<?php
/**
 * Created by PhpStorm.
 * 商品属性可输入格式处理.
 * User: fer
 * Date: 2016/9/27
 * Time: 20:01
 */

namespace App\Services\Goods;

class GoodsInputFormatService
{
    /**
     * 商品属性可输入属性列表
     *
     * @return array
     */
    public function getGoodsInputFormatList()
    {
        $goodsInputFormatArr = config('input_format.goods_attribute');
        $verifyFormatArr = config('input_format.verify_format');

        $reDataArr = [
            'input_format'  => $goodsInputFormatArr,
            'verify_format' => $verifyFormatArr,
        ];

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }
}