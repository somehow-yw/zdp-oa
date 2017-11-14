<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 11:41 AM
 */

namespace App\Repositories\Goods\Contracts;

use App\Models\DpNewGoods;
use Illuminate\Database\Eloquent\Collection;

interface NewGoodsRepository
{
    /**
     * @param $areaId
     * @param $goodsId
     * @param $putOnAt
     * @param $pullOffAt
     *
     * @return DpNewGoods
     */
    public function addNewGoods($areaId, $goodsId, $putOnAt, $pullOffAt);

    /**
     * @param $areaId
     *
     * @return Collection
     */
    public function getGoodsList($areaId);

    /**
     * 下架新上好货
     *
     * @param $id integer 新上好货记录自增id
     *
     * @return bool
     * @throws AppException
     */
    public function pullOffGoods($id);
}