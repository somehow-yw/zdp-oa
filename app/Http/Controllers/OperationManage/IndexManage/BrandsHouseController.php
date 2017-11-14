<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-2-10
 * Time: 上午11:03
 */

namespace app\Http\Controllers\OperationManage\IndexManage;

use App\Http\Controllers\Controller;
use App\Services\OperationManage\IndexManage\BrandsHouseService;
use Illuminate\Http\Request;

class BrandsHouseController extends Controller
{
    /**
     * 添加品牌到品牌馆
     *
     * @param Request            $request
     * @param BrandsHouseService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function addBrands(Request $request, BrandsHouseService $service)
    {
        $putOnAt = $request->input('put_on_at');

        $this->validate(
            $request,
            [
                'area_id'     => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'brand_id'    => 'required|integer|exists:mysql_zdp_main.dp_brands,id',
                'put_on_at'   => 'required|date_format:Y-m-d H:i:s',
                'pull_off_at' => "required|date_format:Y-m-d H:i:s|after:$putOnAt",
                'position'    => 'required|integer|in:1,2,3,4',
                'image'       => 'required|string',
            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'brand_id.required' => '品牌ID必须有',
                'brand_id.integer'  => '品牌ID应该是一个整型',
                'brand_id.exists'   => '品牌id不存在',

                'put_on_at.required'    => '上架时间不能为空',
                'put_on_at.date_format' => '上架时间格式必须满足Y-m-d H:i:s',

                'pull_off_at.required'    => '下架时间不能为空',
                'pull_off_at.date_format' => '下架结束时间格式必须满足Y-m-d H:i:s',
                'pull_off_at.after'       => '下架结束时间必须晚于上架时间',

                'position.required' => '展示位置必须有',
                'position.integer'  => '展示位置应该是一个整型',
                'position.in'       => '展示位置只能是1,2,3',

                'image.required' => '品牌图片必须有',
                'image.string'   => '品牌图片应该是一个字符串',
            ]
        );

        $service->addBrand(
            $request->input('area_id'),
            $request->input('brand_id'),
            $request->input('put_on_at'),
            $request->input('pull_off_at'),
            $request->input('position'),
            $request->input('image')
        );

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }

    /**
     * 获取品牌列表
     *
     * @param Request            $request
     * @param BrandsHouseService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getBrandsList(Request $request, BrandsHouseService $service)
    {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'status'  => 'required|integer|in:0,1,2,3',
                'size'    => 'required|integer|between:1,100',
                'page'    => 'required|integer|between:1,99999',
            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'status.required' => '上架状态必须有',
                'status.integer'  => '上架状态必须是个整数',
                'status.in'       => '上架状态只能是0,1,2,3',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',
            ]
        );

        $reData = $service->getBrandsList(
            (int)$request->input('status', 0),
            $request->input('page'),
            $request->input('area_id')
        );


        return $this->render(
            'index-manage.list',
            $reData,
            'OK'
        );
    }

    /**
     * @param Request            $request
     *
     * @param BrandsHouseService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function pullOffBrands(Request $request, BrandsHouseService $service)
    {
        $this->validate(
            $request,
            [
                'id' => "required|integer|exists:mysql_zdp_main.dp_brands_house,id",
            ],
            [
                'id.required' => '优质供应商记录id必须有',
                'id.integer'  => '优质供应商记录id必须是个整数',
                'id.exists'   => '优质供应商记录id不存在',
            ]
        );

        $service->pullOffBrands(
            $request->input('id')
        );

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }
}