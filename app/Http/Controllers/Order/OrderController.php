<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/9 0009
 * Time: 上午 9:46
 */

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Order\OrderService;
use App\Workflows\OrderWorkflow;
use Validator;

/**
 * 订单处理
 * Class OrderController
 * @package App\Http\Controllers\Order
 */
class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    private $service;
    private $orderWorkflow;

    public function __construct(OrderService $service, OrderWorkflow $orderWorkflow)
    {
        $this->service = $service;
        $this->orderWorkflow =  $orderWorkflow;
    }

    /**
     * 获取订单列表信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_id'=>'integer|min:1|exists:mysql_zdp_main.dp_opder_form,id',
                'order_num'=>'string',
                'order_type'=>'integer',
                'buyer_shop_name'=>'string',
                'seller_shop_name'=>'string',
                'buyer_tel'=>'string',
                'seller_tel'=>'string',
                'page'=>'integer|min:1',
                'size'=>'integer|min:1'
            ],
            [
                'order_id.integer' => '订单编号必须是整数',
                'order_id.min'     => '订单编号必须大于min',
                'order_id.exists'  => '订单编号不存在',

                'order_num.string'=>'子订单号必须是字符串',

                'order_type.integer' => '订单类型必须是整数',

                'buyer_shop_name.string' => '买家店铺名字必须是字符串',

                'seller_shop_name.string' => '卖家店铺名字必须是字符串',

                'buyer_tel.string' => '买家手机号必须是字符串',

                'seller_tel.string' => '卖家手机号必须是整数',

                'page.integer' => '页数必须是整数',
                'page.min'     => '页数必须大于min',

                'size.integer' => '每页显示数量必须是整数',
                'size.min'     => '每页显示数量必须大于min',
            ]
        );

        $resData = $this->service->getList(
            $request->input('order_id', ''),
            $request->input('order_num', ''),
            $request->input('order_type', ''),
            $request->input('buyer_shop_name', ''),
            $request->input('seller_shop_name', ''),
            $request->input('buyer_tel', ''),
            $request->input('seller_tel', ''),
            $request->input('page', 1),
            $request->input('size', 10)
        );

        return response()->json($resData);
    }

    /**
     * 获取订单详情信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num'=>'required|string',
            ],
            [
                'order_num.required'=>'订单号不能为空',
                'order_num.string'=>'订单号必须是字符串'
            ]
        );

        $resData = $this->service->getDetail(
            $request->input('order_num')
        );

        return response()->json($resData);
    }

    /**
     * 订单收款处理
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment(Request $request)
    {
        $this->validate(
            $request,
            [
                'main_order_no' => 'required|string|between:5,64|exists:mysql_zdp_main.dp_opder_form,codenumber',
                'pay_amount'    => 'required|numeric|min:1',

            ],
            [
                'main_order_no.required' => '主订单号不能为空',
                'main_order_no.string'   => '主订单号必须是字符串',
                'main_order_no.between'  => '主订单号不正确',
                'main_order_no.exists'   => '订单不存在',

                'pay_amount.required' => '支付金额不能为空',
                'pay_amount.numeric'  => '支付金额不正确',
                'pay_amount.min'      => '支付金额不可小于:min',
            ]
        );

        $resData = $this->service->payment(
            $request->input('main_order_no'),
            $request->input('pay_amount')
        );

        $responseArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $resData,
        ];

        return response()->json($responseArr);
    }

    /**
     * 获取财务退款的列表信息
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRefundList(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_id'=>'integer|min:1|exists:mysql_zdp_main.dp_opder_form,id',
                'order_num'=>'string',
                'order_type'=>'integer',
                'buyer_shop_name'=>'string',
                'seller_shop_name'=>'string',
                'buyer_tel'=>'string',
                'seller_tel'=>'string',
                'page'=>'integer|min:1',
                'size'=>'integer|min:1'
            ],
            [
                'order_id.integer' => '订单编号必须是整数',
                'order_id.min'     => '订单编号必须大于min',
                'order_id.exists'  => '订单编号不存在',

                'order_num.string'=>'订单号必须是字符串',

                'order_type.integer' => '订单类型必须是整数',

                'buyer_shop_name.string' => '买家店铺名字必须是字符串',

                'seller_shop_name.string' => '卖家店铺名字必须是字符串',

                'buyer_tel.string' => '买家手机号必须是字符串',

                'seller_tel.string' => '卖家手机号必须是整数',

                'page.integer' => '页数必须是整数',
                'page.min'     => '页数必须大于min',

                'size.integer' => '每页显示数量必须是整数',
                'size.min'     => '每页显示数量必须大于min',
            ]
        );

        $resData = $this->service->getRefundList(
            $request->input('order_id', ''),
            $request->input('order_num', ''),
            $request->input('order_type', ''),
            $request->input('buyer_shop_name', ''),
            $request->input('seller_shop_name', ''),
            $request->input('buyer_tel', ''),
            $request->input('seller_tel', ''),
            $request->input('page', 1),
            $request->input('size', 10)
        );

        return response()->json($resData);
    }

    /**
     * 买家取消订单
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyerCancel(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|between:5,64|exists:mysql_zdp_main.dp_opder_form,codenumber',
                'cancel_id'    => 'required|integer|min:1',
                'reason'    => 'required|string',

            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.between'  => '订单号不正确',
                'order_num.exists'   => '订单不存在',

                'cancel_id.required' => '取消原因不能为空',
                'cancel_id.integer'  => '取消原因必须是整数',
                'cancel_id.min'      => '取消原因不可小于:min',

                'reason.required' => '取消原因的补充内容不能为空',
                'reason.integer'  => '取消原因的补充内容必须是整数',
            ]
        );

        $resData = $this->orderWorkflow->buyerCancel(
            $request->input('order_num'),
            $request->input('cancel_id'),
            $request->input('reason')
        );

        return response()->json($resData);
    }

    /**
     * 获取取消订单的理由列表
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReasonList(Request $request)
    {
        $this->validate(
            $request,
            [
                'type' => 'required|integer',
            ],
            [
                'type.required' => '理由类型不能为空',
                'type.integer'   => '理由类型必须是整数',
            ]
        );

        $resData = $this->service->getReasonList(
            $request->input('type')
        );

        return response()->json($resData);
    }

    /**
     * 卖家确认发货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerShipments(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'driver_tel' => 'string',
                'car_num' => 'string',
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'driver_tel.string'   => '司机电话必须是字符串',

                'car_num.string'   => '车牌号必须是字符串',
            ]
        );

        $resData = $this->orderWorkflow->sellerShipments(
            $request->input('order_num'),
            $request->input('driver_tel', ''),
            $request->input('car_num', '')
        );

        return response()->json($resData);
    }

    /**
     * 申请-卖家取消/买家退款/买家退货
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function saleApply(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num'         => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'type'              => 'required|integer|in:1,2,3',
                'cancel_id'         => 'required|integer',
                'cancel_reason'     => 'required|integer',
                'inform_buyer'      => 'required|integer|in:0,1',
                'inform_seller'     => 'required|integer|in:0,1',
                'buyer_cancel_info' => 'required|array',
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'type.required' => '申请类型不能为空',
                'type.integer'  => '申请类型必须是整数',
                'type.in'       => '申请类型只能是1,2,3',

                'cancel_id.required' => '取消原因的ID不能为空',
                'cancel_id.integer'  => '取消原因的ID必须是整数',

                'cancel_reason.required' => '取消原因不能为空',
                'cancel_reason.integer'  => '取消原因必须是整数',

                'inform_buyer.required' => '是否通知买家不能为空',
                'inform_buyer.integer'  => '是否通知买家必须是整数',
                'inform_buyer.in'       => '是否通知买家只能是0,1',

                'inform_seller.required' => '是否通知卖家不能为空',
                'inform_seller.integer'  => '是否通知卖家必须是整数',
                'inform_seller.in'       => '是否通知卖家只能是0,1',

                'buyer_cancel_info.required' => '取消商品不能为空',
                'buyer_cancel_info.array'    => '取消商品必须是数组',
            ]
        );

        foreach ($request->input('buyer_cancel_info') as $key => $value) {
            $validator = Validator::make(
                $value,
                [
                    'id'         => 'required|integer|min:1|exists:mysql_zdp_main.dp_cart_info,id',
                    'cancel_num' => 'required|integer|min:0'
                ],
                [
                    'id.required' => "第{$key}个所取消的商品ID不能为空",
                    'id.integer'  => "第{$key}个所取消的商品ID必须为整型",
                    'id.min'      => "第{$key}个所取消的商品ID不可小于:min",
                    'id.exists'   => "第{$key}个所取消的商品不在此订单中",

                    'cancel_num.required' => "第{$key}个所取消商品个数不能为空",
                    'cancel_num.integer'  => "第{$key}个所取消商品个数必须为一个整数",
                    'cancel_num.min'      => "第{$key}个所取消商品个数必须大于:min",
                ]
            );

            if ($validator->fails()) {
                $message = $validator->errors()->first();
                throw new \Exception($message);
            }
        }

        $resData = $this->orderWorkflow->saleApply(
            $request->input('order_num'),
            $request->input('type'),
            $request->input('cancel_id'),
            $request->input('cancel_reason'),
            $request->input('inform_buyer', 1),
            $request->input('inform_seller', 1),
            $request->input('buyer_cancel_info'),
            $request->getClientIp()
        );

        return response()->json($resData);
    }

    /**
     * 获取退款/退货/取消的金额
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getRefundPrice(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num'  => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'goods_info' => 'array',
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'goods_info.array' => '退款退货商品信息必须是数组',
            ]
        );
        if (!empty($request->input('goods_info'))) {
            foreach ($request->input('goods_info') as $key => $goods) {
                $validator = Validator::make(
                    $goods,
                    [
                        'id'  => 'required|integer|exists:mysql_zdp_main.dp_cart_info,id',
                        'num' => 'required|integer|min:1',
                    ],
                    [
                        'id.required' => '退款退货商品的订单商品ID不能为空',
                        'id.integer'  => '退款退货商品的订单商品ID必须是整数',
                        'id.exists'   => '退款退货商品的订单商品ID不存在',

                        'num.required' => '退款退货商品的数量不能为空',
                        'num.integer'  => '退款退货商品的数量必须是整数',
                        'num.min'      => '退款退货商品的数量必须大于1',
                    ]
                );

                if ($validator->fails()) {
                    $message = $validator->errors()->first();
                    throw new \Exception($message);
                }
            }
        }

        $resData = $this->service->getRefundPrice(
            $request->input('order_num'),
            $request->input('goods_info')
        );

        return response()->json($resData);
    }

    /**
     * 提醒卖家发货
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remindSend(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在'
            ]
        );

        $resData = $this->orderWorkflow->remindSend(
            $request->input('order_num')
        );

        return response()->json($resData);
    }

    /**
     * 提醒买家收货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remindReceive(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在'
            ]
        );
        $resData = $this->orderWorkflow->remindReceive(
            $request->input('order_num')
        );

        return response()->json($resData);
    }

    /**
     * 取消申请-卖家取消/买家退款/买家退货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelApply(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'type'      => 'required|integer|in:1,2,3'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'type.required' => '申请类型不能为空',
                'type.integer'   => '申请类型必须是整数',
                'type.in'   => '申请类型必须是:1,2,3'
            ]
        );
        $resData = $this->orderWorkflow->cancelApply(
            $request->input('order_num'),
            $request->input('type'),
            $request->getClientIp()
        );

        return response()->json($resData);
    }

    /**
     * 同意-卖家取消/买家退款/买家退货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function agreeApply(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'type'      => 'required|integer|in:1,2,3'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'type.required' => '申请类型不能为空',
                'type.integer'   => '申请类型必须是整数',
                'type.in'   => '申请类型必须是:1,2,3'
            ]
        );
        $resData = $this->orderWorkflow->agreeApply(
            $request->input('order_num'),
            $request->input('type'),
            $request->getClientIp()
        );

        return response()->json($resData);
    }

    /**
     * 拒绝-卖家取消/买家退款/买家退货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refuseApply(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'reason_add' => 'string',
                'type'      => 'required|integer|in:1,2,3'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'reason_add.string'   => '拒绝原因必须是字符串',

                'type.required' => '申请类型不能为空',
                'type.integer'   => '申请类型必须是整数',
                'type.in'   => '申请类型必须是:1,2,3'
            ]
        );
        $resData = $this->orderWorkflow->refuseApply(
            $request->input('order_num'),
            $request->input('reason_add'),
            $request->input('type'),
            $request->getClientIp()
        );

        return response()->json($resData);
    }

    /**
     * 卖家确认退款
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerRefund(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'type'      => 'required|integer|in:1,2,3'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'type.required' => '申请类型不能为空',
                'type.integer'   => '申请类型必须是整数',
                'type.in'   => '申请类型必须是:1,2,3'
            ]
        );
        $resData = $this->orderWorkflow->sellerRefund(
            $request->input('order_num'),
            $request->input('type'),
            $request->getClientIp()
        );

        return response()->json($resData);
    }

    /**
     * 买家发货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyerSend(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'driver_tel'      => 'required|integer',
                'car_num'      => 'required|integer',
                'shipment_address'      => 'required|integer',
                'shipment_time'      => 'required|date'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'driver_tel.required' => '司机电话必须输入',
                'driver_tel.string'   => '司机电话为字符串',

                'car_num.required' => '车牌号必须输入',
                'car_num.string'   => '车牌号必须为字符串',

                'shipment_address.required' => '装车地址必须输入',
                'shipment_address.string'   => '装车地址必须为字符串',

                'shipment_time.required'    => '装车时间必须输入',
                'shipment_time.date' => '装车时间格式不正确'
            ]
        );
        $resData = $this->orderWorkflow->buyerSend(
            $request->input('order_num'),
            $request->input('driver_tel'),
            $request->input('car_num'),
            $request->input('shipment_address'),
            $request->input('shipment_time')
        );

        return response()->json($resData);
    }

    /**
     * 买家确认收货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyerDelivery(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在'
            ]
        );
        $resData = $this->orderWorkflow->buyerDelivery(
            $request->input('order_num')
        );

        return response()->json($resData);
    }

    /**
     * 再次申请-卖家取消/买家退款/买家退货
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function againApply(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'type'      => 'required|integer|in:1,2,3'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'type.required' => '申请类型不能为空',
                'type.integer'   => '申请类型必须是整数',
                'type.in'   => '申请类型必须是:1,2,3'
            ]
        );
        $resData = $this->orderWorkflow->againApply(
            $request->input('order_num'),
            $request->input('type')
        );

        return response()->json($resData);
    }

    /**
     * 修改退款金额
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editRefund(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_num' => 'required|string|exists:mysql_zdp_main.dp_opder_form,order_code',
                'money'=>'required|integer|min:1'
            ],
            [
                'order_num.required' => '订单号不能为空',
                'order_num.string'   => '订单号必须是字符串',
                'order_num.exists'   => '订单号不存在',

                'money.required'   => '退款金额不能为空',
                'money.integer'   => '退款金额必须是整数',
                'money.min'   => '退款金额必须大于1'
            ]
        );
        $resData = $this->orderWorkflow->editRefund(
            $request->input('order_num'),
            $request->input('money')
        );

        return response()->json($resData);
    }


    /**
     * 财务撤回
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function financerRecall(Request $request)
    {
        $this->validate(
            $request,
            [
                'refund_num' => 'required|string|exists:mysql_zdp_main.dp_order_refunds,refund_no'
            ],
            [
                'refund_num.required' => '退款编号不能为空',
                'refund_num.string'   => '退款编号必须是字符串',
                'refund_num.exists'   => '退款编号不存在'
            ]
        );
        $resData = $this->orderWorkflow->financerRecall(
            $request->input('refund_num')
        );

        return response()->json($resData);
    }
}
