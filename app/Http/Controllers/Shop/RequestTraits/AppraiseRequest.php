<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/23 0023
 * Time: 下午 4:31
 */

namespace App\Http\Controllers\Shop\RequestTraits;

use App\Exceptions\AppException;
use App\Exceptions\Sellers\SellerException;
use App\Models\DpGoodsConstraints;
use App\Models\DpGoodsTypeSpecialAttribute;
use App\Models\DpPriceRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use League\Flysystem\Exception;
use Validator;

trait AppraiseRequest
{
    /**
     * 订单评价的验证
     *
     * @param Request $request
     */
    protected function appraiseValidate(Request $request)
    {
        // 基本验证
        $this->validate(
            $request,
            [
                'sub_order_no'    => 'required|string|between:6,64',
                'goods_appraises' => 'required|array|min:1',
                'shop_appraises'  => 'required|array|min:2',
            ],
            [

                'sub_order_no.required' => '子订单编号必须有',
                'sub_order_no.string'   => '子订单编号必须是字符串',
                'sub_order_no.between'  => '子订单编号长度不正确',

                'goods_appraises.required' => '请评价货品质量',
                'goods_appraises.array'    => '货品质量评价必须是数组格式',
                'goods_appraises.min'      => '货品质量评价',

                'shop_appraises.required' => '供应商店铺评价必须有',
                'shop_appraises.array'    => '供应商店铺评价必须是数组格式',
                'shop_appraises.min'      => '供应商店铺评价不完整',
            ]
        );
        // 验证商品评价内容完整性
        foreach ($request->input('goods_appraises') as $key => $goodsAppraise) {
            $this->goodsAppraisesValidate($key, $goodsAppraise, $request->input('sub_order_no'));
        }
        // 验证店铺评价完整性
        $this->shopAppraisesValidate($request->input('shop_appraises'));
    }

    /**
     * 商品评价内容验证
     *
     * @param $key
     * @param $goodsAppraise
     * @param $subOrderNo
     *
     * @throws \Exception
     */
    protected function goodsAppraisesValidate($key, $goodsAppraise, $subOrderNo)
    {
        $validator = Validator::make(
            $goodsAppraise,
            [
                'appraise_id' => 'required|integer|min:1',
                'quality'        => 'required|integer|between:1,5',
                'content'        => 'string|max:150',
                'pictures'       => 'array',
            ],
            [

                'appraise_id.required' => "第{$key}个所评价评价ID必须有",
                'appraise_id.integer'  => "第{$key}个所评价评价ID必须为整型",
                'appraise_id.min'      => "第{$key}个所评价评价ID不可小于:min",

                'quality.required' => "第{$key}个商品评价星级必须有",
                'quality.integer'  => "第{$key}个商品评价星级必须为一个整数",
                'quality.between'  => "第{$key}个商品评价星级必须是:min到:max的整数",

                'content.string' => "第{$key}个商品评价内容必须是字符串",
                'content.max'    => "第{$key}个商品评价内容长度必须在:min到:max的范围内",

                'pictures.array'      => "第{$key}个商品评价图片必须是数组",

            ]
        );

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            throw new \Exception($message);
        }
    }

    /**
     * 店铺评价内容验证
     *
     * @param $shopAppraise
     *
     * @throws \Exception
     */
    protected function shopAppraisesValidate($shopAppraise)
    {
        $validator = Validator::make(
            $shopAppraise,
            [
                'sell_service'   => 'required|integer|between:1,5',
                'delivery_speed' => 'required|integer|between:1,5',
            ],
            [
                'sell_service.required' => "请评价服务态度",
                'sell_service.integer'  => "服务态度评价星级必须为一个整数",
                'sell_service.between'  => "服务态度评价星级必须是:min到:max的整数",

                'delivery_speed.required' => "请评价发货速度",
                'delivery_speed.integer'  => "发货速度评价星级必须为一个整数",
                'delivery_speed.between'  => "发货速度评价星级必须是:min到:max的整数",
            ]
        );

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            throw new \Exception($message);
        }
    }
}