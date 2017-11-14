<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/4/7
 * Time: 17:32
 */

namespace App\Exceptions\Appraise;

use App\Exceptions\AppException;

class AppraiseException extends AppException
{
    // 不可重复评价
    const REPEAT_APPRAISE = 101;

    // 没有处理备注不可完成评价的处理
    const NOT_REMARK = 102;

    // 评价信息不存在
    const NOT_APPRAISE = 103;

    // 评价信息不存在
    const APPRAISE_UPLOAD_IMG_PATH_NOT = 403;

    // 评价信息不完整incomplete
    const NOT_INCOMPLETE = 104;

    //商品评价更新失败
    const NOT_UPDATE = 105;

    /**
     * OrderBuy constructor.
     *
     * @param string $message
     * @param null   $code
     */
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}