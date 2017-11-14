<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-2-9
 * Time: 下午5:20
 */

namespace app\Http\Controllers\OperationManage\IndexManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DpBrands;
use App\Models\DpShopInfo;

/**
 * Class CommonController
 * @package app\Http\Controllers\OperationManage\IndexManage
 */
class CommonController extends Controller
{
    /**
     * 根据品牌id获取品牌
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getBrandById(Request $request)
    {
        $this->validate(
            $request,
            [
                'brand_id' => 'required|integer|exists:mysql_zdp_main.dp_brands,id',
            ],
            [
                'brand_id.required' => '品牌id不能为空',
                'brand_id.integer'  => '品牌id必须是个整数',
                'brand_id.exists'   => '品牌id不存在',
            ]
        );

        $brandName = DpBrands::find($request->input('brand_id'))->brand;

        $reData = [
            'brand_name' => $brandName,
        ];

        return $this->render(
            'index-manage.list',
            $reData,
            'OK'
        );
    }

    /**
     * 根据供应商id获取供应商
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getSupplierById(Request $request)
    {
        $this->validate(
            $request,
            [
                'shop_id' => 'required|integer|exists:mysql_zdp_main.dp_shopinfo,shopId',
            ],
            [
                'shop_id.required' => '店铺ID必须有',
                'shop_id.integer'  => '店铺ID应该是一个整型',
                'shop_id.exists'   => '店铺id不存在',
            ]
        );

        $shopName = DpShopInfo::find($request->input('shop_id'))->dianPuName;

        $reData = [
            'shop_name' => $shopName,
        ];

        return $this->render(
            'index-manage.list',
            $reData,
            'OK'
        );
    }
}
