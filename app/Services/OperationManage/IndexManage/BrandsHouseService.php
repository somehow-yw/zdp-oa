<?php
namespace App\Services\OperationManage\IndexManage;

use App\Models\DpBrandsHouse;
use App\Repositories\Brands\Contracts\BrandsHouseRepository;
use App\Services\OperationManage\IndexManage\Traits\ParseStatusFromTime;
use App\Services\OperationManage\IndexManage\Traits\PullOffModel;
use App\Services\OperationManage\IndexManage\Traits\ValidateAreaId;
use App\Services\OperationManage\IndexManage\Traits\ValidateTimeRange;
use Illuminate\Support\Collection;

class BrandsHouseService
{
    use PullOffModel;
    use ValidateTimeRange;
    use ParseStatusFromTime;
    use ValidateAreaId;
    protected $brandsHouseRepo;

    /**
     * BrandsHouseService constructor.
     *
     * @param BrandsHouseRepository $brandsHouseRepo
     */
    public function __construct(BrandsHouseRepository $brandsHouseRepo)
    {
        $this->brandsHouseRepo = $brandsHouseRepo;
    }

    /**
     * 添加品牌
     *
     * @param $areaId     integer  大区id
     * @param $brandId    integer  品牌id
     * @param $putOnAt    string   上架展示时间
     * @param $pullOffAt  string   下架时间
     * @param $position   integer  展示位置
     * @param $image      string   图片
     */
    public function addBrand($areaId, $brandId, $putOnAt, $pullOffAt, $position, $image)
    {
        $this->validateBrandsOverlap($areaId, $brandId, $putOnAt, $pullOffAt, $position);
        $this->brandsHouseRepo->addBrands($areaId, $brandId, $putOnAt, $pullOffAt, $position, $image);
    }

    /**
     * 获取品牌馆列表
     *
     * @param $status
     * @param $page
     * @param $areaId
     *
     * @return array
     */
    public function getBrandsList($status, $page, $areaId)
    {
        $brandsCollection = $this->brandsHouseRepo->getBrandsList($areaId);

        foreach ($brandsCollection as &$item) {
            $this->parseStatus($item);
        }
        if ($status != 0) {
            $brandsCollection = $brandsCollection->where('status', $status);
        }

        $brands = $brandsCollection->forPage($page, request('size'));

        $reData = [
            'page'   => (int)$page,
            'total'  => $brandsCollection->count(),
            'brands' => $brands->values(),
        ];

        return $reData;
    }

    /**
     * @param $id
     */
    public function pullOffBrands($id)
    {
        $model = DpBrandsHouse::find($id);
        $this->pullOff($model);
    }
}
