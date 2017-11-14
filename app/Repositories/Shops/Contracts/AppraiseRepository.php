<?php
namespace App\Repositories\Shops\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AppraiseRepository
{

    /**
     * 获取评价管理列表信息
     *
     * @param string $shop_name 店铺名称
     * @param string $goods_name 商品名称
     * @param string $orderIds 订单id
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param int $size 每页显示的条数
     * @param int $page 页数
     *
     * @return Collection
     */
    public function getList($shop_name, $goods_name, $orderIds, $start_time, $end_time, $size, $page);

    /**
     * @param array $goodsIdToArray 搜索的商品id数组
     *
     * @return Collection
     */
    public function getAppraiseStatus($goodsIdToArray);

    /**
     * @param array $appraiseId 评价id数组
     *
     * @return Collection
     */
    public function getAppraiseStatusLog($appraiseId);

    /**
     * 评价统计（店铺）
     * @param data $startTime  开始时间
     * @param data $endTime    结束时间
     * @param string $province 省
     * @param string $city     市
     * @param string $district 区
     * @param string $seek     搜索条件
     * @param string $seekVal  搜索值
     * @param integer$pageSize 每页显示条数
     * @param integer $pageNum 当前页数
     * @param integer $type    判断当前是供应商还是采购商
     * @param integer $sortType 排序方式：1=>注册册时间排序；2=>交易金额排序；3=>评价数量排序；4=>好评率排序
     * @param string $sortTypeWay 降序还是升序
     * @return mixed
     */
    public function appraiseShopInfo($startTime, $endTime, $province, $city, $district, $seek, $seekVal, $pageSize, $pageNum, $type, $sortType, $sortTypeWay);

    /**
     * 评价统计（物品）
     * @param string $province
     * @param string $city
     * @param string $district
     * @param string $seekVal
     * @param int $pageSize
     * @param int $pageNum
     * @param int $sortType 排序方式：3=>评价数量排序；4=>好评率排序
     * @param string $sortTypeWay
     * @return mixed
     */
    public function appraiseGoodsInfo($startTime, $endTime, $province, $city, $district, $seekVal, $pageSize, $pageNum,$sortType,$sortTypeWay);
    /*
     * @param $orderIds
     *
     * @return Collection
     */
    public function getAppraiseDetails($orderIds);

    /**
     * 根据订单号获取评价的修改日志
     * @param $orderIds
     *
     * @return Collection
     */
    public function getAppraiseLog($orderIds);

    /**
     * 更新评价的日志表
     *
     * @param string $orderIds 子订单号
     * @param string $resObj 修改之前的结果集
     * @param string $remark 修改的备注
     * @param string $status 本次操作的类型说明
     *
     */
    public function updateAppraiseLog($orderIds, $resObj, $remark, $status);

    /**
     * 软删除订单中的商品评价
     * @param string $orderIds 子订单号
     *
     * @return mixed
     */
    public function deleteGoodsAppraise($orderIds);

    /**
     * 软删除店铺（订单）评价
     * @param string $orderIds 子订单号
     *
     * @return mixed
     */
    public function deleteShopAppraise($orderIds);

    /**
     * @param string $subOrderNo 子订单号
     * @param array $shopAppraises 店铺评价数据
     *
     * @return mixed
     */
    public function updateShopAppraise($subOrderNo, $shopAppraises);

    /**
     * @param int $appraiseId 评价ID
     * @param int $quality 评价的星级
     * @param string $content 评价的内容
     *
     * @return mixed
     */
    public function updateGoodsAppraise($appraiseId, $quality, $content);

    /**
     * 更新(直接新加信息)图片信息
     * @param int $appraiseId 评价ID
     * @param string $imgUrl 图片URL
     * @param int $type 类型
     *
     * @return mixed
     */
    public function updateGoodsAppraisePic($appraiseId, $imgUrl, $type);

    /**
     * 根据评价ID删除对应评价的图片
     * @param int $appraiseId 评价ID
     *
     * @return mixed
     */
    public function deleteGoodsAppraisePic($appraiseId);

    /**
     * 重置店铺（订单）评价
     * @param string $orderIds 子订单号
     *
     * @return mixed
     */
    public function resetGoodsAppraise($orderIds);

    /**
     * 重置店铺（订单）评价
     * @param string $orderIds 子订单号
     *
     * @return mixed
     */
    public function resetShopAppraise($orderIds);

}