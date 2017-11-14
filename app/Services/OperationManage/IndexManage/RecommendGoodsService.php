<?php
namespace App\Services\OperationManage\IndexManage;

use App\Exceptions\AppException;
use App\Models\DpRecommendGoods;
use App\Repositories\Goods\Contracts\RecommendGoodsRepository;
use App\Services\OperationManage\IndexManage\Traits\ParseStatusFromTime;
use App\Services\OperationManage\IndexManage\Traits\PullOffModel;
use App\Services\OperationManage\IndexManage\Traits\SwapItem;
use App\Services\OperationManage\IndexManage\Traits\ValidateAreaId;
use App\Services\OperationManage\IndexManage\Traits\ValidateTimeRange;
use Illuminate\Database\Eloquent\Collection;

/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/12/16
 * Time: 4:12 PM
 */
class RecommendGoodsService
{
    use ValidateTimeRange;
    use ParseStatusFromTime;
    use ValidateAreaId;
    use PullOffModel;
    use SwapItem;

    protected $recommendGoodsRepository;

    public function __construct(RecommendGoodsRepository $recommendGoodsRepository)
    {
        $this->recommendGoodsRepository = $recommendGoodsRepository;
    }

    /**
     * 添加推荐商品
     *
     * @param $areaId    integer 片区id
     * @param $goodsId   integer 商品id
     * @param $putOnAt   string  上架展示时间
     * @param $pullOffAt string  下架时间
     *
     * @throws AppException
     */
    public function addRecommendGoods($areaId, $goodsId, $putOnAt, $pullOffAt)
    {
        $this->validateRecommendGoodsTimeRange($goodsId, $putOnAt, $pullOffAt);
        $this->validateGoodsAreaId($areaId, $goodsId);
        $this->recommendGoodsRepository->addGoods($areaId, $goodsId, $putOnAt, $pullOffAt);
    }

    /**
     * 获取片区id下的推荐商品列表
     *
     * @param $status  integer 状态
     * @param $page    integer 当前页数
     * @param $areaId  integer 片区id
     *
     * @return array
     */
    public function getGoodsList($status, $page, $areaId)
    {
        $goodsCollection = $this->recommendGoodsRepository->getGoodsList($areaId);
        foreach ($goodsCollection as &$item) {
            $this->parseStatus($item);
        }

        if ($status != 0) {
            $goodsCollection = $goodsCollection->where('status', $status);
        }
        $goods = $goodsCollection->forPage($page, request('size'));
        $reData = [
            'page'  => (int)$page,
            'total' => $goodsCollection->count(),
            'goods' => $goods->values(),
        ];

        return $reData;
    }

    /**
     * 下架推荐商品
     *
     * @param $id integer 推荐id
     */
    public function pullOffGoods($id)
    {
        $model = DpRecommendGoods::find($id);
        $this->pullOff($model);
    }

    /**
     * 移动推荐商品
     *
     * @param $currentId integer 当前记录id
     * @param $nextId    integer 与之交换的记录id
     */
    public function moveGoods($currentId, $nextId)
    {
        $currentItem = DpRecommendGoods::find($currentId);
        $nextItem = DpRecommendGoods::find($nextId);
        $this->canSwap($currentItem, $nextItem);
        $this->swap($currentItem, $nextItem);
    }
}