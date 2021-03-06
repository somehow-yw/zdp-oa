<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/13/16
 * Time: 11:42 AM
 */

namespace App\Http\Controllers\OperationManage\IndexManage;

use App\Http\Controllers\Controller;
use App\Services\OperationManage\IndexManage\HighQualitySupplierService;
use Illuminate\Http\Request;

class HighQualitySupplierController extends Controller
{
    /**
     * @param Request                    $request
     * @param HighQualitySupplierService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliersList(Request $request, HighQualitySupplierService $service)
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
        $reData = $service->getSuppliersList(
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
     * 添加供应商
     *
     * @param Request                    $request
     * @param HighQualitySupplierService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function addSupplier(Request $request, HighQualitySupplierService $service)
    {
        $putOnAt = $request->input('put_on_at');
        $this->validate(
            $request,
            [
                'area_id'     => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'shop_id'     => 'required|integer|exists:mysql_zdp_main.dp_shopinfo,shopId',
                'put_on_at'   => 'required|date_format:Y-m-d H:i:s',
                'pull_off_at' => "required|date_format:Y-m-d H:i:s|after:$putOnAt",
                'position'    => 'required|integer|in:1,2,3',
                'image'       => 'required|string',
            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'shop_id.required' => '店铺ID必须有',
                'shop_id.integer'  => '店铺ID应该是一个整型',
                'shop_id.exists'   => '店铺id不存在',


                'put_on_at.required'    => '上架时间不能为空',
                'put_on_at.date_format' => '上架时间格式必须满足Y-m-d H:i:s',

                'pull_off_at.required'    => '下架时间不能为空',
                'pull_off_at.date_format' => '下架结束时间格式必须满足Y-m-d H:i:s',
                'pull_off_at.after'       => '下架结束时间必须晚于上架时间',

                'position.required' => '展示位置必须有',
                'position.integer'  => '展示位置应该是一个整型',
                'position.in'       => '展示位置只能是1,2,3',

                'image.required' => '店铺图片必须有',
                'image.string'   => '店铺图片应该是一个字符串',
            ]
        );

        $service->addSupplier(
            $request->input('area_id'),
            $request->input('shop_id'),
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
     * 下架优质供应商
     *
     * @param Request                    $request
     * @param HighQualitySupplierService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function pullOffSupplier(Request $request, HighQualitySupplierService $service)
    {
        $this->validate(
            $request,
            [
                'id' => "required|integer|exists:mysql_zdp_main.dp_high_quality_suppliers,id",
            ],
            [
                'id.required' => '优质供应商记录id必须有',
                'id.integer'  => '优质供应商记录id必须是个整数',
                'id.exists'   => '优质供应商记录id不存在',
            ]
        );
        $service->pullOffSupplier(
            $request->input('id')
        );

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }

    /**
     * 移动优质供应商
     *
     * @param Request                    $request
     * @param HighQualitySupplierService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function moveSupplier(Request $request, HighQualitySupplierService $service)
    {
        $this->validate(
            $request,
            [
                'current_id' => 'required|integer|exists:mysql_zdp_main.dp_high_quality_suppliers,id',
                'next_id'    => 'required|integer|
                different:current_id|exists:mysql_zdp_main.dp_high_quality_suppliers,id',
            ],
            [
                'current_id.required' => '需要移动的推荐商品记录id必须有',
                'current_id.integer'  => '需要移动的推荐商品记录id必须是个整数',
                'current_id.exists'   => '需要移动的推荐商品记录id不存在',

                'next_id.required'  => '与之交换的推荐商品记录id必须有',
                'next_id.different' => '两条记录的id不能相同',
                'next_id.integer'   => '与之交换的推荐商品记录id必须是个整数',
                'next_id.exists'    => '与之交换的推荐商品记录id不存在',
            ]
        );
        $service->moveSupplier(
            $request->input('current_id'),
            $request->input('next_id')
        );

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }
}
