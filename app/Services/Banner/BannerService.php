<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/26
 * Time: 11:13
 */

namespace App\Services\Banner;

use App\Repositories\Banner\Contracts\BannerRepository;
use App\Services\OperationManage\IndexManage\Traits\ParseStatusFromTime;
use App\Services\OperationManage\IndexManage\Traits\ValidateTimeRange;

/**
 * Class BannerService.
 * Banner管理
 *
 * @package App\Services\Banner
 */
class BannerService
{
    use ValidateTimeRange;
    use ParseStatusFromTime;
    private $bannerRepo;

    public function __construct(BannerRepository $bannerRepo)
    {
        $this->bannerRepo = $bannerRepo;
    }

    /**
     * 获取Banner类型
     *
     * @return array
     */
    public function getTypeList()
    {
        $bannerTypeArr = config('banner.types');
        $reDataArr = [
            'banner_types' => $bannerTypeArr,
        ];

        return $reDataArr;
    }

    /**
     * 买家首页Banner 添加
     *
     * @param array $requestDataArr 结构说明：
     *
     *                          [
     *                              'area_id', 片区ID
     *                              'put_on_at', 上架时间
     *                              'pull_off_at', 下架时间
     *                              'position', 展示位置
     *                              'banner_title', 标题
     *                              'cover_pic', 封面图片地址
     *                              'redirect_link', 跳转链接
     *                              'user_name', 添加者名称
     *                              'location' Banner位置
     *                          ]
     *
     * @return array
     */
    public function addBanner(array $requestDataArr)
    {
        $this->validateBannerOverlap(
            $requestDataArr['area_id'],
            $requestDataArr['put_on_at'],
            $requestDataArr['pull_off_at'],
            $requestDataArr['position']
        );

        $addDataCollect = $this->bannerRepo->addBanner($requestDataArr);

        return is_null($addDataCollect) ? [] : $addDataCollect->toArray();
    }

    /**
     * 获取Banner 列表
     *
     * @param array $requestDataArr
     *
     *              [
     *                  'area_id', 片区ID
     *                  'status', 显示状态
     *                  'page', 获取页数
     *                  'size', 获取数量
     *                  'location' 显示位置
     *              ]
     *
     * @return array
     */
    public function getBannerList(array $requestDataArr)
    {
        //pull_off_at 本来该是下架时间，但是需求有变
        //更改为上架时间，即筛选商家时间在一段时间内的
        $selectArr = [
            'id',
            'type_id',
            'title',
            'position',
            'put_on_at',
            'pull_off_at',
            'cover_pic',
            'pv',
        ];
        $bannerDataArr = $this->bannerRepo->getBannerList($requestDataArr, $selectArr);
        $reDataArr = [
            'page'         => (int)$requestDataArr['page'],
            'total'        => $bannerDataArr->total(),
            'banner_lists' => [],
        ];
        if ($bannerDataArr->count()) {
            $bannerListArr = [];
            foreach ($bannerDataArr as $item) {
                $this->parseStatus($item);
                $bannerListArr[] = [
                    'banner_id'    => $item->id,
                    'banner_title' => $item->title,
                    'put_on_at'    => $item->put_on_at,
                    'pull_off_at'  => $item->pull_off_at,
                    'position'     => $item->position,
                    'cover_pic'    => $item->cover_pic,
                    'status'       => $item->status,
                    'pv'           => $item->pv,
                ];
            }
            $reDataArr['banner_lists'] = $bannerListArr;
        }

        return $reDataArr;
    }

    /**
     * 获取Banner 详情
     *
     * @param $bannerId int Banner ID
     *
     * @return array
     */
    public function getBannerInfo($bannerId)
    {
        $selectArr = [
            'id as banner_id',
            'type_id',
            'area_id',
            'cover_pic',
            'title as banner_title',
            'goods_id',
            'shop_id',
            'put_on_at',
            'pull_off_at',
            'content as banner_content',
        ];
        $bannerDataArr = $this->bannerRepo->getBannerInfo($bannerId, $selectArr);

        return is_null($bannerDataArr) ? [] : $bannerDataArr->toArray();
    }

    /**
     * Banner 修改
     *
     * @param array $requestDataArr 结构说明：
     *
     *                          [
     *                              'banner_id', Banner ID
     *                              'type_id', Banner类型ID
     *                              'area_id', 片区ID
     *                              'banner_title', 标题
     *                              'cover_pic', 封面图片地址
     *                              'goods_id', 商品ID
     *                              'shop_id', 店铺ID
     *                              //'put_on_at', 上架时间
     *                              //'pull_off_at', 下架时间
     *                              'banner_content', Banner内容
     *                              'user_name', 添加者名称
     *                              //'location' Banner位置
     *                          ]
     *
     * @return int
     */
    public function updateBannerInfo($requestDataArr)
    {
        $updateNum = $this->bannerRepo->updateBannerInfo($requestDataArr);

        return $updateNum;
    }

    /**
     * 修改Banner 显示顺序（交换排序）
     *
     * @param $upId   int 向上调整的ID
     * @param $downId int 向下调整的ID
     *
     * @return void
     */
    public function updateBannerSort($upId, $downId)
    {
        $this->bannerRepo->updateBannerSort($upId, $downId);
    }

    /**
     * banner上、下架(就是修改上、下架时间)
     *
     * @param $bannerId int Banner ID
     * @param $status   int 上架或下架状态
     *
     * @return int
     */
    public function updateShowTime($bannerId, $status)
    {
        $updateNum = $this->bannerRepo->updateShowTime($bannerId, $status);

        return $updateNum;
    }
}
