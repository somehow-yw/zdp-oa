<?php

namespace App\Exceptions\User;

use App\Exceptions\AppException;
use App\Exceptions\ExceptionCode;

class UserNotExistsException extends AppException
{
    public function __construct($message = '用户不存在')
    {
        parent::__construct($message, ExceptionCode::USER_NOT_EXISTS);
    }
}
