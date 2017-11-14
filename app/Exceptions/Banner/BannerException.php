<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/11/5
 * Time: 10:23
 */

namespace App\Exceptions\Banner;

use App\Exceptions\AppException;

class BannerException extends AppException
{
    /**
     * 已下架Banner 不可重新上架
     */
    const PULL_OFF_NO_PUT_ON = [
        'code'    => 101,
        'message' => '已下架Banner 不可重新上架',
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
