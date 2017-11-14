<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/2/22
 * Time: 14:27
 */

namespace App\Exceptions\System;

use App\Exceptions\AppException;

class VersionException extends AppException
{
    /**
     * 请求IP为空
     * Code for to deal with failure
     *
     * @var int
     */
    const DEVELOP_VERSION_UPDATE_ERROR = [
        'code'    => 101,
        'message' => '开发版本修改错误',
    ];

    public function __construct($exceptionConst, $message = '未定义', $code = 10000)
    {
        if (!empty($exceptionConst['message'])) {
            $message = $exceptionConst['message'];
            $code = $exceptionConst['code'];
        }
        parent::__construct($message, $code);
    }
}
