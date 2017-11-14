<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-2-20
 * Time: 上午10:34
 */

namespace App\Http\Controllers\OperationManage\IndexManage;

use App\Http\Controllers\Controller;
use App\Services\OperationManage\IndexManage\PopupAdsService;
use Illuminate\Http\Request;

class PopupAdsController extends Controller
{
    /**
     * 添加弹窗广告
     *
     * @param Request         $request
     * @param PopupAdsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function addAds(Request $request, PopupAdsService $service)
    {
        $this->validate(
            $request,
            [
                'area_id'     => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'put_on_at'   => 'required|date_format:Y-m-d H:i:s',
                'pull_off_at' => "required|date_format:Y-m-d H:i:s|after:put_on_at",
                'ads_title'   => "required|string",
                'show_time'   => "required|integer|between:3,6",
                'link_url'    => "required|string",
                'image'       => "required|string",

            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'put_on_at.required'    => '上架时间不能为空',
                'put_on_at.date_format' => '上架时间格式必须满足Y-m-d H:i:s',

                'pull_off_at.required'    => '下架时间不能为空',
                'pull_off_at.date_format' => '下架结束时间格式必须满足Y-m-d H:i:s',
                'pull_off_at.after'       => '下架结束时间必须晚于上架时间',

                'ads_title.required' => '广告标题不能为空',
                'ads_title.string'   => '广告标题应该是个字符串',

                'show_time.required' => '广告显示时长不能为空',
                'show_time.integer'  => '广告显示时长是个整数',
                'show_time.between'  => '广告显示时长应该在3-6秒',

                'link_url.required' => '广告链接地址不能为空',
                'link_url.string'   => '广告链接地址应该是个字符串',

                'image.required' => '广告图片不能为空',
                'image.string'   => '广告图片应该是个字符串',
            ]
        );

        $service->addAds($request->all());

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }

    /**
     * 获取弹窗广告列表
     *
     * @param Request         $request
     * @param PopupAdsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getAdsList(Request $request, PopupAdsService $service)
    {
        $this->validate(
            $request,
            [
                'area_id'     => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'status'      => 'required|integer|in:0,1,2,3',
                'put_on_at'   => 'date_format:Y-m-d H:i:s',
                'pull_off_at' => "date_format:Y-m-d H:i:s|after:put_on_at",
                'size'        => 'required|integer|between:1,100',
                'page'        => 'required|integer|between:1,99999',

            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'status.required' => '上架状态必须有',
                'status.integer'  => '上架状态必须是个整数',
                'status.in'       => '上架状态只能是0,1,2,3',

                'put_on_at.required'    => '上架时间上限不能为空',
                'put_on_at.date_format' => '上架时间上限格式必须满足Y-m-d H:i:s',

                'pull_off_at.required'    => '上架时间下限不能为空',
                'pull_off_at.date_format' => '上架时间下限格式必须满足Y-m-d H:i:s',
                'pull_off_at.after'       => '上架时间下限必须晚于上架时间上限',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',
            ]
        );

        $reData = $service->getAdsList(
            $request->input('area_id'),
            (int)$request->input('status', 0),
            (int)$request->input('page'),
            $request->input('put_on_at', null),
            $request->input('pull_off_at', null)
        );

        return $this->render(
            'index-manage.list',
            $reData,
            'OK'
        );
    }

    /**
     * 下架弹窗广告
     *
     * @param Request         $request
     * @param PopupAdsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function pullOffAds(Request $request, PopupAdsService $service)
    {
        $this->validate(
            $request,
            [
                'id' => "required|integer|exists:mysql_zdp_main.dp_popup_ads,id",
            ],
            [
                'id.required' => '弹窗广告记录id必须有',
                'id.integer'  => '弹窗广告记录id必须是个整数',
                'id.exists'   => '弹窗广告记录id不存在',
            ]
        );

        $service->pullOffAds($request->input('id'));

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }
}