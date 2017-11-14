<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-2-10
 * Time: 下午1:48
 */

namespace App\Repositories\Brands;

use App\Models\DpBrandsHouse;
use App\Repositories\Brands\Contracts\BrandsHouseRepository as Contract;
use App\Repositories\Brands\Contracts\Collection;

class BrandsHouseRepository implements Contract
{
    /**
     * @inheritDoc
     */
    public function getBrandsList($areaId)
    {
        return DpBrandsHouse::from('dp_brands_house as bh')
            ->select(
                'bh.id',
                'bh.brand_id',
                'b.brand as brand_name',
                'bh.put_on_at',
                'bh.pull_off_at',
                'bh.pv',
                'bh.position',
                'bh.image'
            )->where('bh.area_id', $areaId)
            ->orderBy('bh.position')
            ->join('dp_brands as b', 'bh.brand_id', '=', 'b.id')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function addBrands($areaId, $brandId, $putOnAt, $pullOffAt, $position, $image)
    {
        DpBrandsHouse::create(
            [
                'area_id'     => $areaId,
                'brand_id'    => $brandId,
                'put_on_at'   => $putOnAt,
                'pull_off_at' => $pullOffAt,
                'position'    => $position,
                'image'       => $image,
            ]
        );
    }
}
