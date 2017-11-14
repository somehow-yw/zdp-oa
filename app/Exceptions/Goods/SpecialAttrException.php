<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/6
 * Time: 12:54
 */

namespace App\Exceptions\Goods;

use App\Exceptions\AppException;

class SpecialAttrException extends AppException
{
    /**
     * 属性不存在
     */
    const ATTR_NOT = 101;

    /**
     * 属性已经存在
     */
    const ATTR_EXISTING = 102;

    /**
     * 属性可输入格式类型不存在
     */
    const ATTR_INPUT_FORMAT_NOT = 103;

    public function __construct($message, $exceptionCode = 10000)
    {
        parent::__construct($message, $exceptionCode);
    }
}