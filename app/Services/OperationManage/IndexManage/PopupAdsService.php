<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-2-20
 * Time: 上午10:37
 */

namespace App\Services\OperationManage\IndexManage;

use App\Models\DpPopupAds;
use App\Services\OperationManage\IndexManage\Traits\ParseStatusFromTime;
use App\Services\OperationManage\IndexManage\Traits\PullOffModel;
use App\Services\OperationManage\IndexManage\Traits\ValidateTimeRange;
use Illuminate\Support\Collection;

class PopupAdsService
{
    use ValidateTimeRange;
    use ParseStatusFromTime;
    use PullOffModel;

    /**
     * @param $requestArr
     *      [
     *      area_id     大区id
     *      put_on_at   上架时间 2016-12-17 08:25:00
     *      pull_off_at 下架时间 2016-12-17 08:25:00
     *      ads_title   广告标题
     *      show_time   显示时长
     *      link_url    链接地址
     *      image      图片地址
     *      ]
     *
     */
    public function addAds($requestArr)
    {
        $areaId = $requestArr['area_id'];
        $putOnAt = $requestArr['put_on_at'];
        $pullOffAt = $requestArr['pull_off_at'];
        $adsTitle = $requestArr['ads_title'];
        $showTime = $requestArr['show_time'];
        $link_url = $requestArr['link_url'];
        $image = $requestArr['image'];
        $this->validatePopupAdsTimeRange($areaId, $putOnAt, $pullOffAt);

        DpPopupAds::create(
            [
                'area_id'     => $areaId,
                'put_on_at'   => $putOnAt,
                'pull_off_at' => $pullOffAt,
                'ads_title'   => $adsTitle,
                'show_time'   => $showTime,
                'link_url'    => $link_url,
                'image'       => $image,
            ]
        );
    }

    /**
     * 获取弹窗广告列表
     *
     * @param             $areaId    integer 大区id
     * @param             $status    integer 状态 0=全部1=待上架2=正在上架3=已下架
     * @param             $page      integer 当前页数
     * @param null|string $putOnAt   上架起始时间
     * @param null|string $pullOffAt 上架结束时间(注：不是下架时间)
     *
     * @return array
     */
    public function getAdsList($areaId, $status, $page, $putOnAt = null, $pullOffAt = null)
    {
        $query = DpPopupAds::where('area_id', $areaId);
        if ($putOnAt) {
            $query = $query->where('put_on_at', '>=', $putOnAt);
        }
        if ($pullOffAt) {
            $query = $query->where('put_on_at', '<', $pullOffAt);
        }
        /** @var Collection $adsCollection */
        $adsCollection = $query->get();
        foreach ($adsCollection as &$item) {
            $this->parseStatus($item);
        }

        if ($status != 0) {
            $adsCollection = $adsCollection->where('status', $status);
        }

        $ads = $adsCollection->forPage($page, request('size'));

        $reData = [
            'page'      => (int)$page,
            'total'     => $adsCollection->count(),
            'popup_ads' => $ads->values(),
        ];

        return $reData;
    }

    /**
     * 下架弹窗广告
     *
     * @param $id integer 记录id
     */
    public function pullOffAds($id)
    {
        $model = DpPopupAds::find($id);
        $this->pullOff($model);
    }
}