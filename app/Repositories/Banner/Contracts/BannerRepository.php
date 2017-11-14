<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/26
 * Time: 12:13
 */

namespace App\Repositories\Banner\Contracts;


interface BannerRepository
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
     *                              'goods_id', 商品ID
     *                              'shop_id', 店铺ID
     *                              'user_name', 添加者名称
     *                              'location' Banner位置
     *                          ]
     *
     * @return \App\Models\DpBannerInfo
     */
    public function addBanner($addDataArr);

    /**
     * 获取Banner 列表
     *
     * @param array $queryDataArr 查询信息
     *
     *              [
     *                  'area_id', 片区ID
     *                  'status', 显示状态
     *                  'size', 获取数量
     *                  'location' 显示位置
     *              ]
     *
     * @param array $selectArr    查询字段:['field1','...']
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function getBannerList(array $queryDataArr, array $selectArr);

    /**
     * 获取Banner 详情
     *
     * @param       $bannerId  int Banner Id
     * @param array $selectArr array 查询字段：['field1','...']
     *
     * @return null|\App\Models\DpBannerInfo
     */
    public function getBannerInfo($bannerId, array $selectArr);

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
    public function updateBannerInfo($updateDataArr);

    /**
     * 修改Banner 显示顺序（交换排序）
     *
     * @param $upId   int 向上调整的ID
     * @param $downId int 向下调整的ID
     *
     * @return void
     */
    public function updateBannerSort($upId, $downId);

    /**
     * banner上、下架(就是修改上、下架时间)
     *
     * @param $bannerId int Banner ID
     * @param $status   int 上架或下架状态
     *
     * @return int
     */
    public function updateShowTime($bannerId, $status);
}
