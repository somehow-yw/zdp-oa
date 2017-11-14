<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/26
 * Time: 9:56
 */

namespace App\Http\Controllers\Common\Banner;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Services\Banner\BannerService;

/**
 * Class BannerController.
 * Banner公用部分
 *
 * @package App\Http\Controllers\Common\Banner
 */
class BannerController extends Controller
{
    /**
     * 获取Banner 类型列表
     *
     * @param BannerService $bannerService
     *
     * @return \Illuminate\Http\Response
     */
    public function getTypeList(BannerService $bannerService)
    {
        $typeArr = $bannerService->getTypeList();

        $dataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $typeArr,
        ];

        return $this->render(
            'banner.list',
            $dataArr['data'],
            $dataArr['message'],
            $dataArr['code']
        );
    }

    /**
     * 获取Banner 详情
     *
     * @param Request       $request
     * @param BannerService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getBannerInfo(Request $request, BannerService $service)
    {
        $this->validate(
            $request,
            [
                'banner_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_banners_info,id',
            ],
            [
                'banner_id.required' => 'BannerID必须传入',
                'banner_id.integer'  => 'BannerID必须是整型',
                'banner_id.min'      => 'BannerID不可小于:min',
                'banner_id.exists'   => 'BannerID不存在',
            ]
        );

        $bannerDataArr = $service->getBannerInfo($request->input('banner_id'));

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $bannerDataArr,
        ];

        return $this->render(
            'banner.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }

    /**
     * 修改Banner 信息
     *
     * @param Request       $request
     * @param BannerService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateBannerInfo(Request $request, BannerService $service)
    {
        $this->validate(
            $request,
            [
                'banner_id'      => 'required|integer|min:1|exists:mysql_zdp_main.dp_banners_info,id',
                'type_id'        => 'required|integer|between:1,4',
                'area_id'        => 'required|integer|min:1',
                'banner_title'   => 'required|string|between:2,50',
                'cover_pic'      => 'required|string|between:5,255',
                'goods_id'       => 'required|integer|min:1',
                'shop_id'        => 'required|integer|min:1',
                //'put_on_at'      => 'required|date_format:Y-m-d H:i:s',
                //'pull_off_at'    => 'required|date_format:Y-m-d H:i:s|after:put_on_at',
                'banner_content' => 'required|string|between:1,65535',
            ],
            [
                'banner_id.required' => 'BannerID必须传入',
                'banner_id.integer'  => 'BannerID必须是整型',
                'banner_id.min'      => 'BannerID不可小于:min',
                'banner_id.exists'   => 'BannerID不存在',

                'type_id.required' => '分类ID必须传入',
                'type_id.integer'  => '分类ID必须是整型',
                'type_id.between'  => '分类ID必须是:min到:max的整数',

                'area_id.required' => '片区ID必须传入',
                'area_id.integer'  => '片区ID必须是整型',
                'area_id.min'      => '片区ID不可小于:min',

                'banner_title.required' => 'Banner标题必须有',
                'banner_title.string'   => 'Banner标题必须是一个字符串',
                'banner_title.between'  => 'Banner标题长度应在:min到:max',

                'cover_pic.required' => '封面图片路径必须有',
                'cover_pic.string'   => '封面图片路径应该是一个字符串',
                'cover_pic.between'  => '封面图片路径长度应在:min到:max',

                'goods_id.required' => '商品ID必须有',
                'goods_id.integer'  => '商品ID应该是一个整型',
                'goods_id.min'      => '商品ID不可小于:min',

                'shop_id.required' => '店铺ID必须有',
                'shop_id.integer'  => '店铺ID应该是一个整型',
                'shop_id.min'      => '店铺ID不可小于:min',

                //'put_on_at.required'    => '上架时间必须有',
                //'put_on_at.date_format' => '上架时间格式必须是 如：2016-12-26 12:00:00',

                //'pull_off_at.required'    => '下架时间必须有',
                //'pull_off_at.date_format' => '下架时间格式必须是 如：2016-12-26 12:00:00',
                //'pull_off_at.after'       => '下架时间必须大于上架时间',

                'banner_content.required' => 'Banner内容必须有',
                'banner_content.string'   => 'Banner内容必须是一个字符串',
                'banner_content.between'  => 'Banner内容长度应在:min到:max',
            ]
        );

        $userInfoArr = $request->user()->toArray();
        $requestDataArr = $request->all();
        $requestDataArr['user_name'] = $userInfoArr['user_name'];
        $updateNum = $service->updateBannerInfo($requestDataArr);

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
     * 修改Banner 显示顺序（交换排序）
     *
     * @param Request       $request
     * @param BannerService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateBannerSort(Request $request, BannerService $service)
    {
        $this->validate(
            $request,
            [
                'up_id'   => 'required|integer|min:1|exists:mysql_zdp_main.dp_banners_info,id',
                'down_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_banners_info,id',
            ],
            [
                'up_id.required' => '向上移动的BannerID必须传入',
                'up_id.integer'  => '向上移动的BannerID必须是整型',
                'up_id.min'      => '向上移动的BannerID不可小于:min',
                'up_id.exists'   => '向上移动的BannerID不存在',

                'down_id.required' => '向下移动的BannerID必须传入',
                'down_id.integer'  => '向下移动的BannerID必须是整型',
                'down_id.min'      => '向下移动的BannerID不可小于:min',
                'down_id.exists'   => '向下移动的BannerID不存在',
            ]
        );

        $service->updateBannerSort($request->input('up_id'), $request->input('down_id'));

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
     * banner上、下架(就是修改上、下架时间)
     *
     * @param Request       $request
     * @param BannerService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateShowTime(Request $request, BannerService $service)
    {
        $this->validate(
            $request,
            [
                'banner_id' => 'required|integer|min:1|exists:mysql_zdp_main.dp_banners_info,id',
                'status'    => 'required|integer|between:2,3',
            ],
            [
                'banner_id.required' => 'BannerID必须传入',
                'banner_id.integer'  => 'BannerID必须是整型',
                'banner_id.min'      => 'BannerID不可小于:min',
                'banner_id.exists'   => 'BannerID不存在',

                'status.required' => '调整状态必须传入',
                'status.integer'  => '调整状态必须是整型',
                'status.min'      => '调整状态应是:min到max的整数',
            ]
        );

        $updateNum = $service->updateShowTime($request->input('banner_id'), $request->input('status'));

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
}
