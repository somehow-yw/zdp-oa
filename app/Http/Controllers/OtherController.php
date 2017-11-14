<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/4
 * Time: 18:11
 */

namespace App\Http\Controllers;

use App\Models\DpGoodsBasicAttribute;
use App\Models\DpGoodsInfo;
use Illuminate\Http\Request;

use App\Services\AreaService;
use App\Services\DailyNewsService;
use App\Services\OssDataDisposeService;
use App\Services\Goods\GoodsInputFormatService;

class OtherController extends Controller
{
    /**
     * 行政区域列表
     *
     * @param AreaService $areaService
     *
     * @return \Illuminate\Http\Response
     */
    public function getAreaList(
        AreaService $areaService
    ) {
        $reData = $areaService->getAreaList();

        return $this->renderTxt(
            'others.list-txt',
            $reData
        );
    }

    /**
     * 每日推文类型获取
     *
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getDailyNewsTypeList(
        DailyNewsService $dailyNewsService
    ) {
        $reData = $dailyNewsService->getDailyNewsTypeList();

        return $this->render(
            'others.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 自定义大区数据获取
     *
     * @param AreaService $areaService
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomAreaList(AreaService $areaService)
    {
        $reData = $areaService->getCustomAreaList();

        return $this->renderTxt(
            'others.list-txt',
            $reData
        );
    }

    /**
     * OSS请求数据签名
     *
     * @param Request               $request
     * @param OssDataDisposeService $ossDataDisposeService
     *
     * @return \Illuminate\Http\Response
     */
    public function getOssDataSignature(
        Request $request,
        OssDataDisposeService $ossDataDisposeService
    ) {
        $this->validate(
            $request,
            [
                'signature_data' => 'required|string|min:5',
            ],
            [
                'signature_data.required' => '需签名的数据必须有',
                'signature_data.string'   => '需签名的数据必须是一个字符串',
                'signature_data.min'      => '需签名的数据不可少于:min个字符',
            ]
        );

        $reData = $ossDataDisposeService->getSignature($request->input('signature_data'));

        return $this->render(
            'others.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 获得OSS请求身份数据
     *
     * @param Request               $request
     * @param OssDataDisposeService $ossDataDisposeService
     *
     * @return \Illuminate\Http\Response
     */
    public function getOssIdentityData(
        Request $request,
        OssDataDisposeService $ossDataDisposeService
    ) {
        $reData = $ossDataDisposeService->getOssIdentityData();

        return $this->render(
            'others.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品属性可输入属性列表
     *
     * @param Request                 $request
     * @param GoodsInputFormatService $goodsInputFormat
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsInputFormatList(Request $request, GoodsInputFormatService $goodsInputFormat)
    {
        $reData = $goodsInputFormat->getGoodsInputFormatList();

        return $this->render(
            'others.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 获取商品单位列表
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsUnits()
    {
        $units = DpGoodsBasicAttribute::getGoodsUnits();
        $data = [];
        foreach ($units as $key => $unit) {
            $data[] = ["id" => $key, "name" => $unit];
        }
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'others.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 商品国别列表获取
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsSmuggles()
    {
        $smuggles = DpGoodsInfo::getSmugglesList();
        $data = [];
        foreach ($smuggles as $key => $smuggle) {
            $data[] = ["id" => $key, "name" => $smuggle];
        }
        $reData = [
            'data'    => $data,
            'message' => 'OK',
            'code'    => 0,
        ];

        return $this->render(
            'others.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}
