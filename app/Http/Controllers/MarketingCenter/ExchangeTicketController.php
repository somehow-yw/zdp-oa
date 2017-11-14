<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 11:48
 */

namespace App\Http\Controllers\MarketingCenter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MarketingCenter\ExchangeTicketService;
use Zdp\Main\Data\Models\DpExchangeTicket;
use Zdp\Main\Data\Models\DpExchangeTicketBuyLog;

/**
 * 兑换券管理控制器
 * Class ExchangeTicketController
 * @package app\Http\Controllers\MarketingCenter
 */
class ExchangeTicketController extends Controller
{
    /**
     * 兑换券分类获取
     *
     * @param \app\Services\MarketingCenter\ExchangeTicketService $ticketService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getType(ExchangeTicketService $ticketService)
    {
        $typeArr = $ticketService->getType();

        $returnArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $typeArr,
        ];

        return response()->json($returnArr);
    }

    /**
     * 兑换券添加
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \App\Services\MarketingCenter\ExchangeTicketService $ticketService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, ExchangeTicketService $ticketService)
    {
        $this->validate(
            $request,
            [
                'type_no'   => 'required|string|between:1,128',
                'image'     => 'required|string|between:5,255',
                'title'     => 'required|string|between:1,255',
                'price'     => 'required|integer|min:1',
                'worth'     => 'required|integer|min:1',
                'sell_time' => 'required|date_format:Y-m-d H:i:s',
                'end_time'  => 'required|date_format:Y-m-d H:i:s|after:sell_time',
            ],
            [
                'type_no.required' => '兑换券类型编号必须有',
                'type_no.string'   => '兑换券类型编号应该是字符串',
                'type_no.between'  => '兑换券类型编号长度应在:min到max之间',

                'image.required' => '兑换券图片地址必须有',
                'image.string'   => '兑换券图片地址必须是字符串',
                'image.between'  => '兑换券图片地址长度应在:min到:max之间',

                'title.required' => '标题必须有',
                'title.string'   => '标题必须字符串',
                'title.between'  => '标题长度应在:min到:max之间',

                'price.required' => '出售价格必须有',
                'price.integer'  => '出售价格必须是一个整型',
                'price.min'      => '出售价格不可小于:min',

                'worth.required' => '兑换券面值必须有',
                'worth.integer'  => '兑换券面值必须是一个整型',
                'worth.min'      => '兑换券面值不可小于:min',

                'sell_time.required'    => '上架时间必须有',
                'sell_time.date_format' => '上架时间格式不正确',

                'end_time.required'    => '下架时间必须有',
                'end_time.date_format' => '下架时间格式不正确',
                'end_time.after'       => '下架时间必须大于上架时间',
            ]
        );
        $ticketService->add($request->all());

        $returnArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($returnArr);
    }

    /**
     * 兑换券上、下架
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \App\Services\MarketingCenter\ExchangeTicketService $ticketService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function onSell(Request $request, ExchangeTicketService $ticketService)
    {
        $this->validate(
            $request,
            [
                'id'          => 'required|integer|min:1|exists:mysql_zdp_main.dp_exchange_tickets,id,deleted_at,NULL',
                'option_type' => 'required_without:sell_time,end_time|integer|in:3',
                'sell_time'   => 'required_without:option_type|date_format:Y-m-d H:i:s',
                'end_time'    => 'required_without:option_type|date_format:Y-m-d H:i:s|after:sell_time',
            ],
            [
                'id.required' => '兑换券ID必须有',
                'id.integer'  => '兑换券ID必须是一个整型',
                'id.min'      => '兑换券ID不可小于:min',
                'id.exists'   => '兑换券不存在',

                'option_type.required_without' => '操作选项类型必须有',
                'option_type.integer'          => '操作选项类型必须是一个整型',
                'option_type.in'               => '操作选项不存在',

                'sell_time.required_without' => '上架时间必须有',
                'sell_time.date_format'      => '上架时间格式不正确',

                'end_time.required_without' => '下架时间必须有',
                'end_time.date_format'      => '下架时间格式不正确',
                'end_time.after'            => '下架时间必须大于上架时间',
            ]
        );
        $ticketService->onSell($request->all());

        $returnArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($returnArr);
    }

    /**
     * 兑换券查询
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \App\Services\MarketingCenter\ExchangeTicketService $ticketService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request, ExchangeTicketService $ticketService)
    {
        $status = implode(',', DpExchangeTicket::getSellStatus());
        $this->validate(
            $request,
            [
                'status'  => "required|integer|in:{$status}",
                'type_no' => 'string|between:1,128',
                'page'    => 'required|integer|min:1',
                'size'    => 'required|integer|between:1,30',
            ],
            [
                'status.required' => '查询状态必须有',
                'status.integer'  => '查询状态必须是一个整型',
                'status.in'       => '查询状态错误',

                'type_no.string'  => '分类编号必须是一个字符串',
                'type_no.between' => '分类编号长度必须在:min到max之间',

                'page.required' => '查询页数必须有',
                'page.integer'  => '查询页数必须是一个整型',
                'page.min'      => '查询页数不可小于:min',

                'size.required' => '返回数量必须有',
                'size.integer'  => '返回数量必须是一个整型',
                'size.between'  => '返回数量应在:min到:max之间',
            ]
        );
        $listArr = $ticketService->getList($request->all());

        $returnArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listArr,
        ];

        return response()->json($returnArr);
    }

    /**
     * 兑换券购买记录查询
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \App\Services\MarketingCenter\ExchangeTicketService $ticketService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyList(Request $request, ExchangeTicketService $ticketService)
    {
        $status = implode(',', DpExchangeTicketBuyLog::getExchangeStatus());
        $this->validate(
            $request,
            [
                'status'  => "integer|in:{$status}",
                'type_no' => 'string|between:1,128',
                'shop_id' => 'integer|min:1|exists:mysql_zdp_main.dp_shopInfo,shopId,state,0',
                'page'    => 'required|integer|min:1',
                'size'    => 'required|integer|between:1,30',
            ],
            [
                'status.integer' => '查询状态必须是一个整型',
                'status.in'      => '查询状态错误',

                'type_no.string'  => '分类编号必须是一个字符串',
                'type_no.between' => '分类编号长度必须在:min到max之间',

                'shop_id.integer' => '店铺ID必须是一个整型',
                'shop_id.min'     => '店铺ID不可小于:min',
                'shop_id.exists'  => '店铺不存在',

                'page.required' => '查询页数必须有',
                'page.integer'  => '查询页数必须是一个整型',
                'page.min'      => '查询页数不可小于:min',

                'size.required' => '返回数量必须有',
                'size.integer'  => '返回数量必须是一个整型',
                'size.between'  => '返回数量应在:min到:max之间',
            ]
        );
        $listArr = $ticketService->buyList($request->all());

        $returnArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listArr,
        ];

        return response()->json($returnArr);
    }

    /**
     * 兑换券购买记录状态更改
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \App\Services\MarketingCenter\ExchangeTicketService $ticketService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateExchangeStatus(Request $request, ExchangeTicketService $ticketService)
    {
        $status = implode(',', DpExchangeTicketBuyLog::getExchangeStatus());
        $this->validate(
            $request,
            [
                'status' => "required|integer|in:{$status}",
                'id'     => 'required|integer|min:1|exists:mysql_zdp_main.dp_exchange_ticket_buy_logs,id',
            ],
            [
                'status.required' => '修改状态必须有',
                'status.integer'  => '修改状态必须是一个整型',
                'status.in'       => '状态错误',

                'id.required' => '记录ID必须有',
                'id.integer'  => '记录ID必须是一个整型',
                'id.min'      => '记录ID不可小于:min',
                'id.exists'   => '修改记录不存在',
            ]
        );
        $listArr = $ticketService->updateExchangeStatus($request->all());

        $returnArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listArr,
        ];

        return response()->json($returnArr);
    }
}
