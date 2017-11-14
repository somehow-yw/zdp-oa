<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 11:39 AM
 */

namespace App\Services\OperationManage\IndexManage;

use App\Models\DpNewGoods;
use App\Repositories\Goods\Contracts\NewGoodsRepository;
use App\Services\OperationManage\IndexManage\Traits\ParseStatusFromTime;
use App\Services\OperationManage\IndexManage\Traits\PullOffModel;
use App\Services\OperationManage\IndexManage\Traits\SwapItem;
use App\Services\OperationManage\IndexManage\Traits\ValidateAreaId;
use App\Services\OperationManage\IndexManage\Traits\ValidateTimeRange;
use Illuminate\Database\Eloquent\Collection;

class NewGoodsService
{
    use ValidateTimeRange;
    use ValidateAreaId;
    use ParseStatusFromTime;
    use PullOffModel;
    use SwapItem;
    protected $newGoodsRepository;

    /**
     * NewGoodsService constructor.
     *
     * @param NewGoodsRepository $newGoodsRepository
     */
    public function __construct(NewGoodsRepository $newGoodsRepository)
    {
        $this->newGoodsRepository = $newGoodsRepository;
    }

    /**
     * 添加新上好货
     *
     * @param $areaId
     * @param $goodsId
     * @param $putOnAt
     * @param $pullOffAt
     */
    public function addNewGoods($areaId, $goodsId, $putOnAt, $pullOffAt)
    {
        $this->validateGoodsAreaId($areaId, $goodsId);
        $this->validateNewGoodsTimeRange($goodsId, $putOnAt, $pullOffAt);
        $this->newGoodsRepository->addNewGoods($areaId, $goodsId, $putOnAt, $pullOffAt);
    }

    /**
     * 获取新上好货列表
     *
     * @param $status  integer 状态
     * @param $page
     * @param $areaId
     *
     * @return array
     */
    public function getNewGoodsList($status, $page, $areaId)
    {
        $newGoodsCollection = $this->newGoodsRepository->getGoodsList($areaId);
        $goods = $newGoodsCollection->toArray()['data'];

        foreach ($goods as &$item) {
            $this->parseStatus($item);
        }
        $goods = new Collection($goods);

        if ($status != 0) {
            $goods = $goods->where('status', $status);
        }

        return [
            'total' => $newGoodsCollection->total(),
            'page'  => $page,
            'goods' => $goods->values(),
        ];
    }

    /**
     * 下架新上好货
     *
     * @param $id
     */
    public function pullOffGoods($id)
    {
        $model = DpNewGoods::find($id);
        $this->pullOff($model);
    }

    /**
     * 移动新上好货
     *
     * @param $currentId integer 当前记录id
     * @param $nextId    integer 与之交换的记录id
     */
    public function moveGoods($currentId, $nextId)
    {
        $currentItem = DpNewGoods::find($currentId);
        $nextItem = DpNewGoods::find($nextId);
        $this->canSwap($currentItem, $nextItem);
        $this->swap($currentItem, $nextItem);
    }
}