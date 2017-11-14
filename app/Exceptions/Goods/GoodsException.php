<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/11/5
 * Time: 10:23
 */

namespace App\Exceptions\Goods;

use App\Exceptions\AppException;
use Zdp\Main\Data\Models\DpGoodsBasicAttribute;

class GoodsException extends AppException
{
    /**
     * 商品信息不正确
     */
    const GOODS_INFO_ERROR = [
        'code'    => 101,
        'message' => '商品信息不正确',
    ];

    /**
     * 商品不存在
     */
    const GOODS_NO = [
        'code'    => 102,
        'message' => '商品不存在',
    ];

    /**
     * 商品国别错误
     */
    const GOODS_SMUGGLE_ERROR = [
        'code'    => 103,
        'message' => '商品国别错误',
    ];

    /**
     * 商品分类不存在
     */
    const GOODS_TYPE_NO = [
        'code'    => 104,
        'message' => '商品分类不存在',
    ];

    /**
     * 商品品牌不存在
     */
    const GOODS_BRAND_NO = [
        'code'    => 105,
        'message' => '商品品牌不存在',
    ];

    /**
     * 商品计量单位错误
     */
    const GOODS_UNIT_ERROR = [
        'code'    => 107,
        'message' => '商品计量单位错误',
    ];

    /**
     * 商品库存错误
     */
    const GOODS_INVENTORY_ERROR = [
        'code'    => 108,
        'message' => '商品库存错误',
    ];

    /**
     * 商品规格错误
     */
    const GOODS_SPEC_ERROR = [
        'code'    => 109,
        'message' => '商品规格错误，不存在或类型错误，请检查',
    ];

    /**
     * 商品型号错误
     */
    const GOODS_TYPE_ERROR = [
        'code'    => 109,
        'message' => '商品型号错误，不存在或类型错误，请检查',
    ];

    /**
     * 商品规格选项错误
     */
    const GOODS_SPEC_OPTION_ERROR = [
        'code'    => 110,
        'message' => '商品规格选项错误',
    ];

    /**
     * 商品规格选项错误
     */
    const GOODS_TYPE_OPTION_ERROR = [
        'code'    => 111,
        'message' => '商品型号选项错误',
    ];

    /**
     * 商品价格错误
     */
    const GOODS_PRICE_ERROR = [
        'code'    => 112,
        'message' => '商品单价小于等于' . DpGoodsBasicAttribute::GOODS_MIN_PRICE . '元，请重新设置单价',
    ];

    public function __construct(array $throwInfo, $message = '')
    {
        if (count($throwInfo)) {
            $message = $throwInfo['message'];
            $exceptionCode = $throwInfo['code'];
        } else {
            $exceptionCode = null;
        }

        parent::__construct($message, $exceptionCode);
    }
}
