<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/26
 * Time: 12:13
 */

namespace App\Repositories\Banner;

use Carbon\Carbon;

use App\Repositories\Banner\Contracts\BannerRepository as RepositoryContract;

use App\Models\DpBannerInfo;

use App\Exceptions\Banner\BannerException;

class BannerRepository implements RepositoryContract
{
    /**
     * Banner 添加
     *
     * @param array $addDataArr 结构说明：
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
     * @return \App\Models\DpBannerInfo
     */
    public function addBanner($addDataArr)
    {
        $addArr = [
            //展示不需要type_id
            'type_id'       => 1,
            //'type_id'     => $addDataArr['type_id'],
            'area_id'       => $addDataArr['area_id'],
            'put_on_at'     => $addDataArr['put_on_at'],
            'pull_off_at'   => $addDataArr['pull_off_at'],
            'position'      => $addDataArr['position'],
            'title'         => $addDataArr['banner_title'],
            'cover_pic'     => $addDataArr['cover_pic'],
            'redirect_link' => $addDataArr['redirect_link'],
            'location'      => "buyer_index",
            'content'       => " ",
            'add_the'       => $addDataArr['user_name'],
        ];

        return DpBannerInfo::create($addArr);
    }

    /**
     * 获取Banner 列表
     *
     * @param array $queryDataArr 查询信息：
     *
     *              [
     *                  'area_id', 片区ID
     *                  'status', 显示状态
     *                  'size', 获取数量
     *                  'location' 显示位置
     *                  'put_on_at'  上架时间
     *                  'pull_off_at' 下架时间
     *              ]
     *
     * @param array $selectArr    查询字段:['field1','...']
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function getBannerList(array $queryDataArr, array $selectArr)
    {
        $query = DpBannerInfo::where('area_id', $queryDataArr['area_id'])
            ->select($selectArr);

        if (isset($queryDataArr['put_on_at'])) {
            $query = $query->where('put_on_at', '>=', $queryDataArr['put_on_at']);
        }

        if (isset($queryDataArr['pull_off_at'])) {
            $query = $query->where('put_on_at', '<', $queryDataArr['pull_off_at']);
        }

        if ($queryDataArr['status']) {
            $newDateTime = date('Y-m-d H:i:s');
            if ($queryDataArr['status'] == DpBannerInfo::AWAIT_PUT_ON) {
                // 待上架
                $query = $query->where('put_on_at', '>', $newDateTime);
            } elseif ($queryDataArr['status'] == DpBannerInfo::PUT_ON) {
                // 上架中
                $query = $query->where('put_on_at', '<=', $newDateTime);
                $query = $query->where('pull_off_at', '>', $newDateTime);
            } elseif ($queryDataArr['status'] == DpBannerInfo::PULL_OFF) {
                // 已下架
                $query = $query->where('pull_off_at', '<=', $newDateTime);
            }
        }

        return $query->orderBy('position', 'asc')->paginate($queryDataArr['size']);
    }

    /**
     * 获取Banner 详情
     *
     * @param       $bannerId  int Banner Id
     * @param array $selectArr array 查询字段：['field1','...']
     *
     * @return null|\App\Models\DpBannerInfo
     */
    public function getBannerInfo($bannerId, array $selectArr)
    {
        return DpBannerInfo::where('id', $bannerId)
            ->select($selectArr)
            ->first();
    }

    /**
     * Banner 修改
     *
     * @param array $updateDataArr 结构说明：
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
    public function updateBannerInfo($updateDataArr)
    {
        $updateArr = [
            'type_id'   => $updateDataArr['type_id'],
            'area_id'   => $updateDataArr['area_id'],
            'title'     => $updateDataArr['banner_title'],
            'cover_pic' => $updateDataArr['cover_pic'],
            'goods_id'  => $updateDataArr['goods_id'],
            'shop_id'   => $updateDataArr['shop_id'],
            //'location'    => $addDataArr['location'],
            //'put_on_at'   => $addDataArr['put_on_at'],
            //'pull_off_at' => $addDataArr['pull_off_at'],
            'content'   => $updateDataArr['banner_content'],
            'add_the'   => $updateDataArr['user_name'],
        ];

        return DpBannerInfo::where('id', $updateDataArr['banner_id'])
            ->update($updateArr);
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
        $selectArr = ['id', 'position'];
        $upCollect = $this->getBannerInfo($upId, $selectArr);
        $downCollect = $this->getBannerInfo($downId, $selectArr);

        if ($upCollect->position > $downCollect->position) {
            $upPositionTemp = $upCollect->position;
            $upCollect->position = $downCollect->position;
            $upCollect->save();

            $downCollect->position = $upPositionTemp;
            $downCollect->save();
        }
    }

    /**
     * banner上、下架(就是修改上、下架时间)
     *
     * @param $bannerId int Banner ID
     * @param $status   int 上架或下架状态
     *
     * @throws BannerException
     * @return int
     */
    public function updateShowTime($bannerId, $status)
    {
        $selectArr = ['id', 'put_on_at', 'pull_off_at'];
        $bannerCollect = $this->getBannerInfo($bannerId, $selectArr);

        $newDateTime = date('Y-m-d H:i:s');
        if (DpBannerInfo::PUT_ON == $status) {
            // 需要上架
            if ($bannerCollect->pull_off_at <= $newDateTime) {
                // 已下架
                throw new BannerException(BannerException::PULL_OFF_NO_PUT_ON);
            } else {
                $bannerCollect->put_on_at = $newDateTime;
            }
        }
        if (DpBannerInfo::PULL_OFF == $status) {
            // 需要下架
            if ($bannerCollect->put_on_at > $newDateTime) {
                // 还未上架
                $bannerCollect->put_on_at = $newDateTime;
                $bannerCollect->pull_off_at = $newDateTime;
            } elseif ($bannerCollect->pull_off_at > $newDateTime) {
                // 上架中
                $bannerCollect->pull_off_at = $newDateTime;
            }
        }

        return $bannerCollect->save();
    }
}
