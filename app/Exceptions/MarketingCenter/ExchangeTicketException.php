<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 14:39
 */

namespace App\Exceptions\MarketingCenter;

use App\Exceptions\AppException;

/**
 * 兑换券异常说明
 * Class ExchangeTicketException
 * @package App\Exceptions\MarketingCenter
 */
class ExchangeTicketException extends AppException
{
    const TYPE_ERROR = [
        'code'    => '101',
        'message' => '兑换券分类错误',
    ];

    const EXCHANGE_STATUS_CANNOT = [
        'code'    => '102',
        'message' => '不可更改为此兑换状态',
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
