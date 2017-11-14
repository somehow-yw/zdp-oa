<?php

/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/8/27
 * Time: 18:25
 */

namespace App\Repositories\DailyNews;

use Carbon\Carbon;

use App\Repositories\DailyNews\Contracts\MessageSendUsersRepository as RepositoriesContract;

use App\Models\DpServiceMessageSendUser;
use App\Models\DpShangHuInfo;
use App\Models\DpPianquDivide;

class MessageSendUsersRepository implements RepositoriesContract
{
    /**
     * 获取可接收客服消息推送的用户
     *
     * @see \App\Repositories\DailyNews\Contracts\MessageSendUsersRepository::getDailyNewsReceiveUsers()
     *
     * @param int $areaId 大区ID
     * @param int $size   数据获取量
     *
     * @return object
     */
    public function getDailyNewsReceiveUsers($areaId, $size)
    {
        // 可接收客服消息的最后互动时间
        $endInteractTime = date('Y-m-d H:i:s', time() - (3600 * 48));
        $query = DpServiceMessageSendUser::with(
            [
                'user' => function ($query) {
                    $query->with('shop')
                        ->select('OpenID', 'shId', 'lianxiTel', 'unionName', 'shopId')
                        ->where('shengheAct', DpShangHuInfo::STATUS_PASS);
                },
            ]
        )
            ->where(function ($query) use ($areaId) {
                $query = $query->where('shop_area_id', $areaId);
                if ($areaId == DpPianquDivide::SICHUAN_AREA) {
                    $query->orWhere('shop_area_id', DpPianquDivide::PARENT_COMPANY);
                }
            })
            ->where('updated_at', '>', $endInteractTime);
        $receiveUserInfoObjs = $query->paginate($size);

        return $receiveUserInfoObjs;
    }
}