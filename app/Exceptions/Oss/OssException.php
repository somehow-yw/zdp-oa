<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/11/15
 * Time: 18:54
 */

namespace App\Exceptions\Oss;

use App\Exceptions\AppException;

/**
 * Class OssException.
 * OSS操作错误信息
 * @package App\Exceptions
 */
class OssException extends AppException
{
    const BUCKET_NULL = [
        'code'    => '101',
        'message' => 'bucket不可为空',
    ];

    const OSS_OBJECT_NULL = [
        'code'    => '102',
        'message' => 'object不可为空',
    ];

    const OPTIONS_ERROR = [
        'code'    => '103',
        'message' => 'options参数不正确',
    ];

    const FILE_NULL = [
        'code'    => '104',
        'message' => 'file不可为空',
    ];

    const OSS_OPTION_NOT_ARR = [
        'code'    => '105',
        'message' => 'option必须为数组',
    ];

    const OSS_BUCKET_ERROR = [
        'code'    => '106',
        'message' => '未通过Bucket名称规则校验',
    ];

    const OSS_OBJECT_ERROR = [
        'code'    => '106',
        'message' => '未通过Object名称规则校验',
    ];

    public function __construct($errorConst, $message = '', $code = null)
    {
        if (!empty($errorConst['code'])) {
            $message = $errorConst['message'];
            $code = $errorConst['code'];
        }
        parent::__construct($message, $code);
    }
}