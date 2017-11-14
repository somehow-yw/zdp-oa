<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 12:08 PM
 */

namespace App\Services\OperationManage\IndexManage\Traits;

use Carbon\Carbon;

trait ParseStatusFromTime
{
    public function parseStatus(&$item)
    {
        $nowCarbon = Carbon::now();
        $putOnCarbon = new Carbon($item['put_on_at']);
        $pullOffCarbon = new Carbon($item['pull_off_at']);
        //待上架
        if ($putOnCarbon->gt($nowCarbon)) {
            $item['status'] = 1;
            //正在上架
        } elseif ($putOnCarbon->lte($nowCarbon) && $pullOffCarbon->gt($nowCarbon)) {
            $item['status'] = 2;
            //已下架
        } elseif ($pullOffCarbon->lt($nowCarbon)) {
            $item['status'] = 3;
        }
    }
}
