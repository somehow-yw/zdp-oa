<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 4:06 PM
 */

namespace App\Services\OperationManage\IndexManage\Traits;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Exceptions\AppException;

trait PullOffModel
{
    /**
     * @param $model Model
     *
     * @return bool|null
     * @throws AppException
     */
    public function pullOff($model)
    {
        $carbonNow = Carbon::now();
        $putOnAt = new Carbon($model->put_on_at);
        $pullOffAt = new Carbon($model->pull_off_at);
        //还没开始展示 直接删除该条记录
        if ($putOnAt->gt($carbonNow)) {
            return $model->delete();
        }
        //已经自动下架
        if ($pullOffAt->lte($carbonNow)) {
            throw new AppException("该条记录已经自动下架");
        }

        $model->pull_off_at = time_now();

        return $model->save();
    }
}