<?php
namespace App\Http\Controllers\OperationManage\IndexManage;

use App\Http\Controllers\Controller;
use App\Services\OperationManage\IndexManage\RecommendGoodsService;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/12/16
 * Time: 4:08 PM
 */
class RecommendGoodsController extends Controller
{
    /**
     * @param Request               $request
     * @param RecommendGoodsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function addGoods(Request $request, RecommendGoodsService $service)
    {
        $putOnAt = $request->input('put_on_at');
        $this->validate(
            $request,
            [
                'area_id'     => 'required|integer|exists:mysql_zdp_main.dp_pianqu_divide,id',
                'goods_id'    => 'required|integer|exists:mysql_zdp_main.dp_goods_info,id',
                'put_on_at'   => 'required|date_format:Y-m-d H:i:s',
                'pull_off_at' => "required|date_format:Y-m-d H:i:s|after:$putOnAt",

            ],
            [
                'area_id.required' => '片区id不能为空',
                'area_id.integer'  => '片区id必须为一个整数',
                'area_id.exists'   => '片区id不存在',

                'goods_id.required' => '商品ID必须有',
                'goods_id.integer'  => '商品ID应该是一个整型',
                'goods_id.exists'   => '商品id不存在',


                'put_on_at.required'    => '上架时间不能为空',
                'put_on_at.date_format' => '上架时间格式必须满足Y-m-d H:i:s',

                'pull_off_at.required'    => '下架时间不能为空',
                'pull_off_at.date_format' => '下架结束时间格式必须满足Y-m-d H:i:s',
                'pull_off_at.after'       => '下架结束时间必须晚于上架时间',

            ]
        );
        $service->addRecommendGoods(
            $request->input('area_id'),
            $request->input('goods_id'),
            $request->input('put_on_at'),
            $request->input('pull_off_at')
        );


        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }

    /**
     * @param Request               $request
     * @param RecommendGoodsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getGoodsList(Request $request, RecommendGoodsService $service)
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
        $reData = $service->getGoodsList(
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
     * 下架推荐商品
     *
     * @param Request               $request
     * @param RecommendGoodsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function pullOffGoods(Request $request, RecommendGoodsService $service)
    {
        $this->validate(
            $request,
            [
                'id' => "required|integer|exists:mysql_zdp_main.dp_recommend_goods,id",
            ],
            [
                'id.required' => '推荐商品记录id必须有',
                'id.integer'  => '推荐商品记录id必须是个整数',
                'id.exists'   => '推荐商品记录id不存在',
            ]
        );

        $service->pullOffGoods(
            $request->input('id')
        );

        return $this->render(
            'index-manage.list',
            [],
            'OK'
        );
    }

    /**
     * 移动推荐好货
     *
     * @param Request               $request
     * @param RecommendGoodsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function moveGoods(Request $request, RecommendGoodsService $service)
    {
        $this->validate(
            $request,
            [
                'current_id' => 'required|integer|exists:mysql_zdp_main.dp_recommend_goods,id',
                'next_id'    => 'required|integer|different:current_id|exists:mysql_zdp_main.dp_recommend_goods,id',
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

        $service->moveGoods(
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
