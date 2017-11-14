<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/8/30
 * Time: 15:30
 */

namespace App\Exceptions\User;

use App\Exceptions\AppException;

/**
 * 店铺异常信息处理
 * Class ShopException
 * @package App\Exceptions\User
 */
class ShopException extends AppException
{
    /**
     * 此手机号未注册
     */
    const MEMBER_MOBILE_NOT = [
        'code'    => 101,
        'message' => '该手机号未注册不能添加',
    ];

    /**
     * 店铺不存在
     */
    const SHOP_NOT = [
        'code'    => 102,
        'message' => '店铺不存在',
    ];

    /**
     * 店铺不可用
     */
    const SHOP_CLOSE = [
        'code'    => 103,
        'message' => '店铺不可用',
    ];

    /**
     * 店铺不可绑定成员
     */
    const SHOP_NOT_BAND_MEMBER = [
        'code'    => 104,
        'message' => '此店铺不可绑定成员',
    ];

    /**
     * 此店铺大老板已不在关注公众号
     */
    const SHOP_BOOS_UNSUBSCRIBE = [
        'code'    => 105,
        'message' => '此店铺大老板已不在关注公众号',
    ];

    /**
     * 此会员已不在关注公众号
     */
    const MEMBER_UNSUBSCRIBE = [
        'code'    => 106,
        'message' => '此会员已不在关注公众号',
    ];

    /**
     * 有成员的店铺不可被其它店铺绑定为成员
     */
    const BAND_SHOP_EXIST_MEMBER = [
        'code'    => 107,
        'message' => '该被绑定的店铺有成员，请先删除所有成员后再次绑定',
    ];

    /**
     * 已经是此店铺下的成员，不可重复绑定
     */
    const ALREADY_SHOP_MEMBER = [
        'code'    => 108,
        'message' => '已经是此店铺下的成员，不可重复绑定',
    ];

    /**
     * 不可将自己绑定到自己的店铺下
     */
    const SHOP_MEMBER_SAME = [
        'code'    => 109,
        'message' => '不可将自己绑定到自己的店铺下',
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
