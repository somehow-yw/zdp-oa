<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/29/16
 * Time: 3:26 PM
 */

namespace App\Exceptions\Goods;

final class ActivityException
{
    const ACTIVITY_TIME_OVERLAP_CODE = 101;
    const ACTIVITY_TIME_OVERLAP_MSG  = "添加的秒杀活动时间与已有秒杀活动有重叠";
    const ACTIVITY_TYPE_ID_NOT_FOUND = 102;
}