<?php

namespace App\Exceptions\User;

use App\Exceptions\AppException;
use App\Exceptions\ExceptionCode;

class UserExistsException extends AppException
{
    public function __construct($message = '用户已经存在')
    {
        parent::__construct($message, ExceptionCode::USER_EXISTS);
    }
}
