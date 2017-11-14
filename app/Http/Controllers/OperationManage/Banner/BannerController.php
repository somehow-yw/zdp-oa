<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/26
 * Time: 11:44
 */

namespace App\Http\Controllers\OperationManage\Banner;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Services\Banner\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * 买家首页Banner 添加
     *
     * @param Request       $request
     * @param BannerService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function addBuyerIndexBanner(Request $request, BannerService $service)
    {
        $this->validate(
            $request,
            [
                'area_id'       => 'required|integer|min:1',
                'put_on_at'     => 'required|date_format:Y-m-d H:i:s',
                'pull_off_at'   => 'required|date_format:Y-m-d H:i:s|after:put_on_at',
                'position'      => 'required|integer|between:1,5',
                'banner_title'  => 'required|string|between:2,50',
                'cover_pic'     => 'string|between:5,255',
                'redirect_link' => 'required|string|between:5,512',
            ],
            [
                'area_id.required' => '片区ID必须传入',
                'area_id.integer'  => '片区ID必须是整型',
                'area_id.min'      => '片区ID不可小于:min',

                'put_on_at.required'    => '上架时间必须有',
                'put_on_at.date_format' => '上架时间格式必须是 如：2016-12-26 12:00:00',

                'pull_off_at.required'    => '下架时间必须有',
                'pull_off_at.date_format' => '下架时间格式必须是 如：2016-12-26 12:00:00',
                'pull_off_at.after'       => '下架时间必须大于上架时间',

                'position.required' => '展示位置必须有',
                'position.integer'  => '展示位置必须是一个整形',
                'position.between'  => '展示位置只能在1-5',

                'banner_title.required' => 'Banner标题必须有',
                'banner_title.string'   => 'Banner标题必须是一个字符串',
                'banner_title.between'  => 'Banner标题长度应在:min到:max',

                'cover_pic.string'  => '封面图片路径应该是一个字符串',
                'cover_pic.between' => '封面图片路径长度应在:min到:max',

                'redirect_link.required' => '跳转链接必须有',
                'redirect_link.string'   => '跳转链接应该是一个字符串',
                'redirect_link.between'  => '跳转链接长度应在:min到:max',
            ]
        );

        $userInfoArr = $request->user()->toArray();
        $requestDataArr = $request->all();
        $requestDataArr['user_name'] = $userInfoArr['user_name'];
        $requestDataArr['location'] = 'buyer_index';
        $newBannerDataArr = $service->addBanner($requestDataArr);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $this->render(
            'banner.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }

    /**
     * 买家首页banner列表获取
     *
     * @param Request       $request
     * @param BannerService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getBuyerIndexBanner(Request $request, BannerService $service)
    {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1',
                'status'  => 'required|integer|between:0,3',
                'page'    => 'required|integer|min:1',
                'size'    => 'required|integer|between:1,50',
            ],
            [
                'area_id.required' => '片区ID必须传入',
                'area_id.integer'  => '片区ID必须是整型',
                'area_id.min'      => '片区ID不可小于:min',

                'status.required' => '显示状态必须有',
                'status.integer'  => '显示状态必须是整数',
                'status.between'  => '显示状态应是:min到:max的整数',

                'page.required' => '获取页数必须有',
                'page.integer'  => '获取页数应该是一个整型',
                'page.min'      => '获取页数不可小于:min',

                'size.required' => '获取数量必须传入',
                'size.integer'  => '获取数量必须是整型',
                'size.between'  => '获取数量必须是:min到:max的整数',
            ]
        );
        $requestDataArr = $request->all();
        $requestDataArr['location'] = 'buyer_index';
        $listDataArr = $service->getBannerList($requestDataArr);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listDataArr,
        ];

        return $this->render(
            'banner.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }
}
