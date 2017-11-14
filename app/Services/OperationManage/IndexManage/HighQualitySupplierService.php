<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/13/16
 * Time: 11:50 AM
 */

namespace App\Services\OperationManage\IndexManage;

use App\Models\DpHighQualitySupplier;
use App\Repositories\Shops\Contracts\HighQualitySupplierRepository;
use App\Services\OperationManage\IndexManage\Traits\ParseStatusFromTime;
use App\Services\OperationManage\IndexManage\Traits\PullOffModel;
use App\Services\OperationManage\IndexManage\Traits\SwapItem;
use App\Services\OperationManage\IndexManage\Traits\ValidateAreaId;
use App\Services\OperationManage\IndexManage\Traits\ValidateTimeRange;
use Illuminate\Database\Eloquent\Collection;

class HighQualitySupplierService
{
    use PullOffModel;
    use ValidateTimeRange;
    use ParseStatusFromTime;
    use ValidateAreaId;
    use SwapItem;
    protected $supplierRepository;

    public function __construct(HighQualitySupplierRepository $highQualitySupplierRepository)
    {
        $this->supplierRepository = $highQualitySupplierRepository;
    }

    /**
     * 添加优质供应商
     *
     * @param $areaId      integer 大区ID
     * @param $shopId      integer 店铺ID
     * @param $putOnAt     string  上架展示时间
     * @param $pullOffAt   string  下架时间
     * @param $position    integer 展示位置
     * @param $image       string  图片地址
     *
     * @return void
     */
    public function addSupplier($areaId, $shopId, $putOnAt, $pullOffAt, $position, $image)
    {
        $this->validateSupplierOverlap($areaId, $shopId, $putOnAt, $pullOffAt, $position);
        $this->validateShopAreaId($areaId, $shopId);
        $this->supplierRepository->addSupplier($areaId, $shopId, $putOnAt, $pullOffAt, $position, $image);
    }

    /**
     * 获取优质供应商列表
     *
     * @param $status
     * @param $page
     * @param $areaId
     *
     * @return array
     */
    public function getSuppliersList($status, $page, $areaId)
    {
        $suppliersCollection = $this->supplierRepository->getSuppliersList($areaId);

        foreach ($suppliersCollection as &$item) {
            $this->parseStatus($item);
        }
        if ($status != 0) {
            $suppliersCollection = $suppliersCollection->where('status', $status);
        }

        $suppliers = $suppliersCollection->forPage($page, request('size'));

        $reData = [
            'page'      => (int)$page,
            'total'     => $suppliersCollection->count(),
            'suppliers' => $suppliers->values(),
        ];

        return $reData;
    }

    /**
     * 下架优质供应商
     *
     * @param $id
     */
    public function pullOffSupplier($id)
    {
        $model = DpHighQualitySupplier::find($id);
        $this->pullOff($model);
    }

    /**
     * 移动优质供应商
     *
     * @param $currentId
     * @param $nextId
     */
    public function moveSupplier($currentId, $nextId)
    {
        $currentItem = DpHighQualitySupplier::find($currentId);
        $nextItem = DpHighQualitySupplier::find($nextId);
        $this->canSwap($currentItem, $nextItem);
        $this->swap($currentItem, $nextItem);
    }
}