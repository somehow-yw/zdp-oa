<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/13/16
 * Time: 1:38 PM
 */

namespace App\Repositories\Shops;

use App\Models\DpHighQualitySupplier;
use App\Repositories\Shops\Contracts\HighQualitySupplierRepository as Contract;
use Carbon\Carbon;
use App\Exceptions\AppException;

class HighQualitySupplierRepository implements Contract
{
    /**
     * @inheritDoc
     */
    public function getSuppliersList($areaId)
    {
        return DpHighQualitySupplier::from('dp_high_quality_suppliers as h')
            ->select(
                'h.id',
                'h.shop_id',
                's.dianPuName as shop_name',
                'h.put_on_at',
                'h.pull_off_at',
                'h.pv',
                'h.position',
                'h.image'
            )->where('h.area_id', $areaId)
            ->orderBy('h.position')
            ->join('dp_shopinfo as s', 'h.shop_id', '=', 's.shopId')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function addSupplier($areaId, $shopId, $putOnAt, $pullOffAt, $position, $image)
    {
        return DpHighQualitySupplier::create(
            [
                'area_id'     => $areaId,
                'shop_id'     => $shopId,
                'put_on_at'   => $putOnAt,
                'pull_off_at' => $pullOffAt,
                'position'    => $position,
                'image'       => $image,
            ]
        );
    }

    /**
     * 下架优质供应商
     *
     * @param $id integer 优质供应商记录自增Id
     *
     * @return bool
     * @throws AppException
     */
    public function pullOffSupplier($id)
    {
        /** @var DpHighQualitySupplier $highQualitySupplier */
        $highQualitySupplier = DpHighQualitySupplier::find($id);
        $carbonNow = Carbon::now();
        $putOnAt = new Carbon($highQualitySupplier->put_on_at);
        $pullOffAt = new Carbon($highQualitySupplier->pull_off_at);
        //还没开始展示 直接删除该条记录
        if ($putOnAt->gt($carbonNow)) {
            return $highQualitySupplier->delete();
        }
        //已经自动下架
        if ($pullOffAt->lte($carbonNow)) {
            throw new AppException("优质供应商已经自动下架");
        }

        $highQualitySupplier->pull_off_at = time_now();

        return $highQualitySupplier->save();
    }
}