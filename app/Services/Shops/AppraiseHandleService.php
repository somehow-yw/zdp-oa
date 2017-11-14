<?php

namespace App\Services\Shops;

use App\Models\DpAppraiseDisposeLog;
use App\Models\DpShopInfo;
use Carbon\Carbon;
use DB;
use App\Models\DpAppraiseImgs;
use App\Models\DpGoodsAppraises;
use App\Models\DpServiceAppraises;
use App\Models\DpOpderForm;
use App\Models\DpCartInfo;
use App\Repositories\Shops\AppraiseRepository;
use App\Exceptions\Appraise\AppraiseException;
use App\Utils\AliCloudOss\OssAliossChatUtil;
use Illuminate\Support\Facades\App;


class AppraiseHandleService
{
    private $appraiseRep;

    public function __construct(AppraiseRepository $appraiseRepository)
    {
        $this->appraiseRep = $appraiseRepository;
    }

    /**
     *评价数据统计（店铺）
     *
     * @param data    $startTime
     * @param data    $endTime
     * @param string  $province
     * @param string  $city
     * @param string  $district
     * @param string  $seek
     * @param string  $seekVal
     * @param integer $pageSize
     * @param integer $pageNum
     * @param integer $type
     * @param integer $sortType
     * @param string  $sortTypeWay 倒序还是降序
     *
     * @return mixed
     */
    public function appraiseShopInfo(
        $startTime,
        $endTime,
        $province,
        $city,
        $district,
        $seek,
        $seekVal,
        $pageSize,
        $pageNum,
        $type,
        $sortType,
        $sortTypeWay
    ) {
        $reData = $this->appraiseRep->appraiseShopInfo(
            $startTime,
            $endTime,
            $province,
            $city,
            $district,
            $seek,
            $seekVal,
            $pageSize,
            $pageNum,
            $type,
            $sortType,
            $sortTypeWay
        );
        foreach($reData as $item){
            $goodAppraiseNum = DpGoodsAppraises::query()
                ->where('sell_shop_id',$item->shopId)
                ->where('quality',DpGoodsAppraises::FIVE)
                ->count('id');
            $info['name']= $item->name;
            $info['ZhuCe']= $item->ZhuCe;
            $info['shopId']= $item->shopId;
            $info['allSale']= empty($item->allSale)? '0' : $item->allSale;
            $info['appraiseNum']= empty($item->appraiseNum)? '0' : $item->appraiseNum;
            if($info['appraiseNum'] == 0){
                $info['goodAppraise'] = '0%';
            }else{
                $info['goodAppraise']=round($goodAppraiseNum/$item->appraiseNum,4)*100 .'%';
            }

            $info['deliverySpeed']= empty($item->serAppraise->deliverySpeed) ? '0' : $item->serAppraise->deliverySpeed;
            $info['servicePlatform']= empty($item->serAppraise->servicePlatform) ? '0' : $item->serAppraise->servicePlatform;
            if($seek == 'dianPuName' && !empty($seekVal)){
                $query = DpGoodsAppraises::query()->where('sell_shop_id',$item->shopId);
                $mediumQuery = clone $query;
                $poorQuery = clone $query;
                if($info['appraiseNum'] != 0){
                    //好评数据
                    $info['goodAppraiseNum'] = $goodAppraiseNum;
                    $info['hasImgGoodAppraiseNum']=$query->where('quality',DpGoodsAppraises::FIVE)
                        ->where('hasImg',DpGoodsAppraises::IMG_APPRAISE)
                        ->count('id');
                    //中评数据
                    $info['mediumAppraiseNum'] = $mediumQuery->whereIn('quality',DpGoodsAppraises::$mediumAppraiseArr)->count('id');
                    $info['mediumAppraise'] = round($info['mediumAppraiseNum']/$info['appraiseNum'],4)*100 .'%';
                    $info['hasImgMediumAppraiseNum'] = $mediumQuery->where('hasImg',DpGoodsAppraises::IMG_APPRAISE)->count('id');
                    //差评数据
                    $info['poorAppraiseNum'] = $poorQuery->whereIn('quality',DpGoodsAppraises::$poorAppraiseArr)->count('id');
                    $info['poorAppraise'] = round($info['poorAppraiseNum']/$info['appraiseNum'],4)*100 .'%';
                    $info['hasImgPoorAppraiseNum'] = $poorQuery->where('hasImg',DpGoodsAppraises::IMG_APPRAISE)->count('id');
                }else{
                    //好评数据
                    $info['goodAppraiseNum'] = '0';
                    $info['hasImgGoodAppraiseNum']='0';
                    //中评数据
                    $info['mediumAppraiseNum'] = '0';
                    $info['mediumAppraise'] = '0%';
                    $info['hasImgMediumAppraiseNum'] = '0';
                    //差评数据
                    $info['poorAppraiseNum'] = '0';
                    $info['poorAppraise'] = '0%';
                    $info['hasImgPoorAppraiseNum'] = '0';
                }
            }
            $appraiseInfo[] = $info;
        }
        $reData->data = $appraiseInfo;
        $allRatio = $this->appraiseRep->meanAppraise();
        return $reData;
    }

    /**
     * 评论统计（商品）
     *
     * @param data   $startTime
     * @param data   $endTime
     * @param string $province
     * @param string $city
     * @param string $district
     * @param string $seekVal
     * @param int    $pageSize
     * @param int    $pageNum
     * @param int    $sortType
     * @param string $sortTypeWay
     *
     * @return mixed
     */
    public function appraiseGoodsInfo(
        $startTime,
        $endTime,
        $province,
        $city,
        $district,
        $seekVal,
        $pageSize,
        $pageNum,
        $sortType,
        $sortTypeWay
    ) {
        $reData = $this->appraiseRep->appraiseGoodsInfo(
            $startTime,
            $endTime,
            $province,
            $city,
            $district,
            $seekVal,
            $pageSize,
            $pageNum,
            $sortType,
            $sortTypeWay
        );
        foreach ($reData['data'] as &$item) {
            if ($sortType = DpShopInfo::GOODAPPRAISE) {
                $item = $this->objToArr($item);
            }
            $item['appraiseGood'] = round($item['appraiseGood'], 2) . '%';
            $serAppraisesQuery = DpServiceAppraises::query();
            $serverCount = $serAppraisesQuery
                ->where('sell_shop_id', $item['shopid'])
                ->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $endTime)
                ->count();
            if (!empty($serverCount)) {
                $deliverySpeed = round($serAppraisesQuery->where('sell_shop_id',
                        $item['shopid'])->sum('delivery_speed') / $serverCount,
                    2);
                $servicePlatform =
                    round($serAppraisesQuery->where('sell_shop_id',
                            $item['shopid'])->sum('service_platform') /
                          $serverCount, 2);
                $item['deliverySpeed'] = $deliverySpeed;
                $item['servicePlatform'] = $servicePlatform;
            } else {
                $item['deliverySpeed'] = '0';
                $item['servicePlatform'] = '0';
            }
            $saleNumQuery = DpCartInfo::query();
            $saleNum = $saleNumQuery->where('addtime', '>=', $startTime)
                                    ->where('addtime', '<=', $endTime)
                                    ->where('goodid', $item['id'])
                                    ->whereIn('good_act',
                                        DpCartInfo::$statistics)
                                    ->sum('buy_num');
            if (!empty($saleNum)) {
                $item['saleNum'] = $saleNum;
            } else {
                $item['saleNum'] = 0;
            }
        }

        return $reData;
    }


    /**
     * 获取评价管理中的列表信息
     *
     * @param $shop_name
     * @param $goods_name
     * @param $orderIds
     * @param $start_time
     * @param $end_time
     * @param $size
     * @param $page
     *
     * @return array
     * @throws AppraiseException
     */
    public function getList(
        $shop_name,
        $goods_name,
        $orderIds,
        $start_time,
        $end_time,
        $size,
        $page
    ) {

        //获取列表数据
        $resObj =
            $this->appraiseRep->getList($shop_name, $goods_name, $orderIds,
                $start_time, $end_time, $size, $page);
        if (empty($resObj)) {
            throw new AppraiseException('结果不存在',
                AppraiseException::APPRAISE_UPLOAD_IMG_PATH_NOT);
        }
        $resData = $resObj->toArray();

        foreach ($resData['data'] as $k => $v) {
            $resData['data'][$k]['gname'] =
                $v['gname'] . "等" . $v['good_num'] . "件商品";
            if (empty($v['goods_appraise'])) {
                $resData['data'][$k]['appraise_status'] = "未评价";
            } else {
                $resData['data'][$k]['appraise_status'] = "已评价";
                foreach ($v['goods_appraise'] as $k1 => $v1) {
                    if ($v1['pid'] > DpGoodsAppraises::FATHER_APPRAISE) {
                        $resData['data'][$k]['appraise_status'] = "已追加";
                    }
                }

                if (!empty($v['appraise_log'])) {
                    foreach ($v['appraiseLog'] as $k2 => $v2) {
                        switch ($v2['status']) {
                            case 0:
                                $string = "已修改";
                                break;
                            case 1:
                                $string = "已删除";
                                break;
                            case 2:
                                $string = "未评价";
                                break;
                        }
                        $resData['data'][$k]['appraise_status'] = $string;
                    }
                }
            }
        }

        $reDataArr = [
            'lists'   => $resData['data'],
            'total'   => $resObj->total(),
            'current' => $resObj->currentPage(),
        ];

        return [
            'data'    => $reDataArr,
            'message' => 'ok',
            'code'    => 0,
        ];
    }

    /*
     * 判断订单的评价状态
     */
    public function judgeAppraiseState($orderCodeIdToArray)
    {
        foreach ($orderCodeIdToArray as $key => $value) {
            $resDate[$key]['sub_order_no'] = $value;
            $resObj = DpGoodsAppraises::query()
                                      ->where('sub_order_no', $value)
                                      ->get();
            if ($resObj->isEmpty()) {
                $resDate[$key]['appraise_status'] = '未评价';
            }
            foreach ($resObj as $item) {
                $resDate[$key]['appraise_status'] = '已评价';
                if ($item['pid'] !== 0) {
                    $resDate[$key]['appraise_status'] = '已追加';
                }
            }
        }

        //判断评价是否修改
        $statusUpdate =
            $this->appraiseRep->getAppraiseStatusLog($orderCodeIdToArray)
                              ->toArray();
        if (!empty($statusUpdate)) {
            foreach ($resDate as $k => $v) {
                foreach ($statusUpdate as $k1 => $v1) {
                    if ($v['sub_order_no'] == $v1['sub_order_no']) {
                        switch ($v1['status']) {
                            case 0:
                                $string = "已修改";
                                break;
                            case 1:
                                $string = "已删除";
                                break;
                            case 2:
                                $string = "未评价";
                                break;
                        }
                        $resDate[$k]['appraise_status'] = $string;
                    }
                }
            }
        }

        return $resDate;
    }


    /**
     *采购商评价数据采购
     *
     * @param data    $startTime
     * @param data    $endTime
     * @param string  $province
     * @param string  $city
     * @param string  $district
     * @param string  $seek
     * @param string  $seekVal
     * @param integer $pageSize
     * @param integer $pageNum
     *
     * @return mixed
     */
    public function appraiseInfo(
        $startTime,
        $endTime,
        $province,
        $city,
        $district,
        $seek,
        $seekVal,
        $pageSize,
        $pageNum
    ) {
        $reData =
            $this->appraiseRep->appraiseInfo($startTime, $endTime, $province,
                $city, $district, $seek, $seekVal, $pageSize, $pageNum);
        foreach ($reData['data'] as &$item) {
            $query = DpGoodsAppraises::query();
            $appraiseNum = $query
                ->where('sell_shop_id', $item['shopId'])
                ->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $endTime)
                ->count();
            if (!empty($appraiseNum)) {
                $item['appraiseNum'] = $appraiseNum;
                $goodAppraiseQuery = clone $query;
                $goodAppraise = round(($goodAppraiseQuery->where('quality',
                        DpGoodsAppraises::FIVE)->count()) / $appraiseNum, 2);
                $item['goodAppraise'] = $goodAppraise * 100 . '%';
            } else {
                $item['appraiseNum'] = 0;
                $item['goodAppraise'] = 0;
            }


            $serappraisesQuery = DpServiceAppraises::query();
            $serverCount = $serappraisesQuery
                ->where('sell_shop_id', $item['shopId'])
                ->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $endTime)
                ->count();
            if (!empty($serverCount)) {
                $deliverySpeed = round($serappraisesQuery->where('sell_shop_id',
                        $item['shopId'])->sum('delivery_speed') / $serverCount,
                    2);
                $servicePlatform =
                    round($serappraisesQuery->where('sell_shop_id',
                            $item['shopId'])->sum('service_platform') /
                          $serverCount, 2);
                $item['deliverySpeed'] = $deliverySpeed;
                $item['servicePlatform'] = $servicePlatform;
            } else {
                $item['deliverySpeed'] = '0';
                $item['servicePlatform'] = '0';
            }

        }
        $re = collect($reData['data'])->sortByDesc('appraiseNum')->values();
        $reData['data'] = $re;

        return $reData;
    }

    /**
     * 根据订单号获取对应的评价详情
     *
     * @param $orderIds
     *
     * @return array
     * @throws AppraiseException
     */
    public function getAppraiseDetails($orderIds)
    {
        //获取信息列表
        $resObj = $this->appraiseRep->getAppraiseDetails($orderIds)->toArray();
        if (empty($resObj)) {
            throw new AppraiseException('订单号不存在',
                AppraiseException::APPRAISE_UPLOAD_IMG_PATH_NOT);
        }

        $resData = '';
        //整合返回的数据
        $resData['shop_appraise']['sell_service'] =
            $resObj['order_appraise']['sell_service'];
        $resData['shop_appraise']['delivery_speed'] =
            $resObj['order_appraise']['delivery_speed'];

        foreach ($resObj['order_goods'] as $k => $v) {
            $resData['goods_appraise'][$k]['id'] = $v['id'];
            $resData['goods_appraise'][$k]['good_new_price'] =
                $v['good_new_price'];
            $resData['goods_appraise'][$k]['buy_num'] = $v['buy_num'];
            $resData['goods_appraise'][$k]['count_price'] = $v['count_price'];
            $resData['goods_appraise'][$k]['meter_unit'] = $v['meter_unit'];
            $resData['goods_appraise'][$k]['name'] = $v['goods']['gname'];
            foreach ($resObj['goods_appraise'] as $k1 => $v1) {
                if ($v['coid'] == $v1['sub_order_no'] &&
                    $v['id'] == $v1['order_goods_id']
                ) {
                    $resData['goods_appraise'][$k]['appraise_id'] = $v1['id'];
                    $resData['goods_appraise'][$k]['quality'] = $v1['quality'];
                    $resData['goods_appraise'][$k]['content'] = $v1['content'];
                    $resData['goods_appraise'][$k]['img'] = $v1['appraise_img'];
                }
            }
        }

        return $resData;
    }

    /**
     * 更新（修改）评价信息
     *
     * @param       $subOrderNo
     * @param array $goodsAppraises
     * @param array $shopAppraises
     *
     * @return array
     * @throws \Exception
     */
    public function updateOrderAppraise(
        $subOrderNo,
        array $goodsAppraises,
        array $shopAppraises
    ) {

        $remark = "修改评价信息";
        $status = DpAppraiseDisposeLog::STATUS_ALTER;

        // 根据订单号获得订单信息
        $orderStatusArr = [
            DpOpderForm::TAKE_ORDER,
            DpOpderForm::WITHDRAW_BEING_PROCESSED_ORDER,
            DpOpderForm::WITHDRAW_ACCOMPLISH_ORDER,
        ];
        $orderInfo = DpOpderForm::query()
                                ->where('order_code', $subOrderNo)
                                ->whereIn('orderact', $orderStatusArr)
                                ->select([
                                    'id',
                                    'method',
                                    'buy_realpay',
                                    'good_num',
                                    'uid',
                                    'shopid',
                                    'addtime',
                                ])
                                ->first();
        if (is_null($orderInfo)) {
            throw new AppraiseException('此订单还不可评价',
                AppraiseException::NOT_INCOMPLETE);
        }

        //根据订单获取评价信息
        $resObj = $this->getAppraiseDetails($subOrderNo);

        DB::transaction(
            function () use (
                $subOrderNo,
                $shopAppraises,
                $goodsAppraises,
                $orderInfo,
                $resObj,
                $remark,
                $status
            ) {

                //更新店铺（订单）评价信息
                $this->appraiseRep->updateShopAppraise($subOrderNo,
                    $shopAppraises);

                //更新商品评价信息
                $this->handleGoodsAppraise($goodsAppraises);

                //将此次操作写入日志
                $this->appraiseRep->updateAppraiseLog($subOrderNo, $resObj,
                    $remark, $status);
            }
        );


        return [
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ];
    }

    /**
     * 评价的更新处理
     *
     * @param array $goodsAppraises 商品评价数据
     *
     * @throws AppraiseException
     *
     */
    public function handleGoodsAppraise($goodsAppraises)
    {

        foreach ($goodsAppraises as $k => $v) {
            //更新数据库商品评价信息
            $reUpdate =
                $this->appraiseRep->updateGoodsAppraise($v['appraise_id'],
                    $v['quality'], $v['content']);
            if (empty($reUpdate)) {
                throw new AppraiseException('商品评价更新失败',
                    AppraiseException::NOT_UPDATE);
            }

            //图片处理

            // 根据评价ID获取图片
            $appraisePic = DpAppraiseImgs::query()
                                         ->where("appraise_id",
                                             $v['appraise_id'])
                                         ->get()
                                         ->toArray();

            // 1、删除原有的图片
            if (!empty($appraisePic)) {
                $this->appraiseRep->deleteGoodsAppraisePic($v['appraise_id']);
            }
            // 2、将新图片添加
            if (!empty($v['pictures'])) {
                foreach ($v['pictures'] as $k2 => $v2) {
                    $this->appraiseRep->updateGoodsAppraisePic($v['appraise_id'],
                        $v2['img_url'], DpAppraiseImgs::GOODS_APPRAISES);
                }
            }
        }
    }


    /**
     * 从oss上删除替换的图片
     *
     * @param $url
     *
     * @return string
     * @throws AppraiseException
     */
    public function deletePicFromOSS($url)
    {
        //配置OSS URL基本参数
        $options = config('oss.options');
        $ossBucket = config('oss.oss_bucket');
        $ossObj = App::make(OssAliossChatUtil::class);
        $ossReData = $ossObj->delete_object($ossBucket, $url, $options);
        $ossReDataJson = json_encode($ossReData, JSON_UNESCAPED_UNICODE);
        $ossErrorArr = json_decode($ossReDataJson, true);
        if ($ossErrorArr['message']['status'] != 200) {
            $ossReData = $ossObj->delete_object($ossBucket, $url, $options);
            $ossReDataJson = json_encode($ossReData, JSON_UNESCAPED_UNICODE);
            $ossErrorArr = json_decode($ossReDataJson, true);
            if ($ossErrorArr['message']['status'] != 200) {
                throw new AppraiseException('图片替换失败',
                    AppraiseException::NOT_INCOMPLETE);
            }
        }

        return "ok";
    }

    /**
     * 根据订单获取评价的修改日志
     *
     * @param string $orderIds 订单号
     *
     * @return array
     * @throws AppraiseException
     */
    public function getAppraiseLog($orderIds)
    {

        $resData = '';
        $resObj = $this->appraiseRep->getAppraiseLog($orderIds);
        if (empty($resObj)) {
            throw new AppraiseException('订单号不存在',
                AppraiseException::APPRAISE_UPLOAD_IMG_PATH_NOT);
        }

        //整合返回的数据
        foreach ($resObj as $k => $v) {
            $content = \GuzzleHttp\json_decode($v->content);
            $resData[$k]['dispose_person_id'] = $v->admin_id;
            $resData[$k]['dispose_person_name'] = $v->admin_name;
            $resData[$k]['dispose_time'] = $v->updated_at;
            $resData[$k]['appraise_data'] = $content;
        }

        return [
            'data'    => $resData,
            'message' => 'ok',
            'code'    => 0,
        ];
    }

    /**
     * 软删除评价
     *
     * @param $orderIds
     *
     * @return array
     * @throws AppraiseException
     */
    public function deleteAppraise($orderIds)
    {
        $remark = "删除评价信息";
        $status = DpAppraiseDisposeLog::STATUS_DELETE;
        $resObj = $this->getAppraiseDetails($orderIds);
        if (empty($resObj)) {
            throw new AppraiseException('订单号不存在',
                AppraiseException::APPRAISE_UPLOAD_IMG_PATH_NOT);
        }

        DB::transaction(
            function () use ($orderIds, $resObj, $remark, $status) {
                //将此次操作写入日志
                $this->appraiseRep->updateAppraiseLog($orderIds, $resObj,
                    $remark, $status);

                //软删除订单对应的商品评价
                $this->appraiseRep->deleteGoodsAppraise($orderIds);

                //软删除店铺（订单）评价
                $this->appraiseRep->deleteShopAppraise($orderIds);

                // 更新订单中的评价状态
                $this->updateOrderAppraiseStatus($orderIds, DpOpderForm::HAS_APPRAISE_DEL);
            }
        );

        //

        return [
            'data'    => [],
            'message' => 'ok',
            'code'    => 0,
        ];
    }

    /**
     * 重置评价
     *
     * @param $orderIds
     *
     * @return array
     * @throws AppraiseException
     */
    public function resetAppraise($orderIds)
    {
        $remark = "重置评价信息";
        $status = DpAppraiseDisposeLog::STATUS_RESET;
        $resObj = $this->getAppraiseDetails($orderIds);
        if (empty($resObj)) {
            throw new AppraiseException('订单号不存在',
                AppraiseException::APPRAISE_UPLOAD_IMG_PATH_NOT);
        }

        DB::transaction(
            function () use ($orderIds, $resObj, $remark, $status) {
                // 将此次操作写入日志
                $this->appraiseRep->updateAppraiseLog($orderIds, $resObj,
                    $remark, $status);

                // 重置订单对应的商品评价
                $this->appraiseRep->resetGoodsAppraise($orderIds);

                // 重置店铺（订单）评价
                $this->appraiseRep->resetShopAppraise($orderIds);

                // 更新订单中的评价状态
                $this->updateOrderAppraiseStatus($orderIds, DpOpderForm::HAS_APPRAISE_RESET);
            }
        );

        //

        return [
            'data'    => [],
            'message' => 'ok',
            'code'    => 0,
        ];
    }

    /**
     * 评价的操作需更新的订单状态
     * @param $orderIds int 子订单号
     * @param $appraiseStatus int 订单中的评价状态
     */
    public function updateOrderAppraiseStatus($orderIds, $appraiseStatus){
        $updateData = [
            'has_appraise'=>$appraiseStatus,
            'change_appraise'=>Carbon::now()->format('Y-m-d H:i:s')
        ];
        DpOpderForm::query()
            ->where('order_code', $orderIds)
            ->update($updateData);
    }

    /**对象转数组
     *
     * @param $obj
     *
     * @return mixed
     */
    public function objToArr($obj)
    {
        if (is_object($obj)) {
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
        } else {
            $arr = $obj;
        }

        return $arr;
    }
}