<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/4
 * Time: 11:20
 */

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Services\TradeService;

class TradeController extends Controller
{
    /**
     * 商贸公司列表
     *
     * @param Request      $request
     * @param TradeService $tradeService
     *
     * @return \Illuminate\Http\Response
     */
    public function getTradeList(
        Request $request,
        TradeService $tradeService
    ) {
        $this->validate(
            $request,
            [
                'page' => 'required|integer|between:1,99999',
                'size' => 'required|integer|between:1,100',
            ],
            [
                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',
            ]
        );

        $reData = $tradeService->getTradeList($request->input('page'), $request->input('size'));

        return $this->renderTxt(
            'trade.list-txt',
            $reData
        );
    }

    /**
     * 商贸公司添加
     *
     * @param Request      $request
     * @param TradeService $tradeService
     *
     * @return \Illuminate\Http\Response
     */
    public function addTrade(
        Request $request,
        TradeService $tradeService
    ) {
        $this->validate(
            $request,
            [
                'data' => 'required|string|between:20,999999',
            ],
            [

                'data.required' => '请求数据必须有',
                'data.string'   => '请求数据必须是一个JSON串',
                'data.between'  => '请求数据长度必须在:min, 到:max间',
            ]
        );
        $requestDataArr = json_decode($request->input('data'), true);
        if (json_last_error() != 0) {
            return $this->render(
                'trade.list',
                [
                    'code'    => json_last_error(),
                    'message' => json_last_error_msg(),
                    'data'    => [],
                ]
            );
        }
        // 做数据验证
        $messages = $this->tradeAddVerify($requestDataArr);
        if (count($messages)) {
            return $this->renderError($messages[0]);
        }

        $reData = $tradeService->addTrade($requestDataArr);

        return $this->renderTxt(
            'trade.list-txt',
            $reData
        );
    }

    /**
     * 商贸公司详细信息获取
     *
     * @param Request      $request
     * @param TradeService $tradeService
     *
     * @return \Illuminate\Http\Response
     */
    public function getTradeInfo(
        Request $request,
        TradeService $tradeService
    ) {
        $this->validate(
            $request,
            [
                'trade_id' => 'required|integer|between:1,999999',
            ],
            [

                'trade_id.required' => '公司ID必须有',
                'trade_id.integer'  => '公司ID必须是一个整型',
                'trade_id.between'  => '公司ID必须在:min, 到:max间',
            ]
        );

        $reData = $tradeService->getTradeInfo($request->input('trade_id'));

        return $this->renderTxt(
            'trade.list-txt',
            $reData
        );
    }

    /**
     * 商贸公司详细信息修改
     *
     * @param Request      $request
     * @param TradeService $tradeService
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTradeInfo(
        Request $request,
        TradeService $tradeService
    ) {
        $this->validate(
            $request,
            [
                'data' => 'required|string|between:20,999999',
            ],
            [

                'data.required' => '请求数据必须有',
                'data.string'   => '请求数据必须是一个JSON串',
                'data.between'  => '请求数据长度必须在:min, 到:max间',
            ]
        );
        $requestDataArr = json_decode($request->input('data'), true);
        if (json_last_error() != 0) {
            return $this->render(
                'trade.list',
                [
                    'code'    => json_last_error(),
                    'message' => json_last_error_msg(),
                    'data'    => [],
                ]
            );
        }
        // 做数据验证
        $messages = $this->tradeUpdateVerify($requestDataArr);
        if (count($messages)) {
            return $this->renderError($messages[0]);
        }

        $reData = $tradeService->updateTradeInfo($request->input('data'));

        return $this->renderTxt(
            'trade.list-txt',
            $reData
        );
    }

    /**
     * 商贸公司状态修改
     *
     * @param Request      $request
     * @param TradeService $tradeService
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTradeStatus(
        Request $request,
        TradeService $tradeService
    ) {
        $this->validate(
            $request,
            [
                'trade_id' => 'required|integer|between:1,999999',
                'status'   => 'required|integer|between:1,3',
            ],
            [

                'trade_id.required' => '公司ID必须有',
                'trade_id.integer'  => '公司ID必须是一个整型',
                'trade_id.between'  => '公司ID必须在:min, 到:max间',

                'status.required' => '修改状态必须有',
                'status.integer'  => '修改状态必须是一个整型',
                'status.between'  => '修改状态必须在:min, 到:max间',
            ]
        );
        $reData = $tradeService->updateTradeStatus($request->input('trade_id'), $request->input('status'));

        return $this->renderTxt(
            'trade.list-txt',
            $reData
        );
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getFeesTypeList()
    {
        $reData = config('trade.fees_rule_types');

        return $this->render(
            'trade.list',
            $reData
        );
    }

    /**
     * 商贸公司添加时的数据验证
     *
     * @param array $requestDataArr 需验证的规则
     *
     * @return array
     */
    private function tradeAddVerify($requestDataArr)
    {
        // 基本结构数据验证
        $messages = $this->tradeBasicDataArrVerify($requestDataArr);
        if (count($messages)) {
            return $messages;
        }

        // 公司账户信息验证
        $validator = Validator::make(
            $requestDataArr['trade_infos'],
            [
                'shop_id'        => 'required|integer|between:1,99999999',
                'login_name'     => 'required|mobile',
                'login_password' => 'required|string|between:6,16',
            ],
            [
                'shop_id.required' => '店铺ID必须有',
                'shop_id.integer'  => '店铺ID必须是一个整型',
                'shop_id.between'  => '店铺ID必须是:min, 到:max的整数',

                'login_name.required' => '登录账号名必须有',
                'login_name.mobile'   => '登录账号名必须是一个手机号',

                'login_password.required' => '账号登录密码必须有',
                'login_password.string'   => '账号登录密码必须是一个字符串',
                'login_password.between'  => '账号登录密码长度必须在:min, 到:max之间',
            ]
        );
        $errors = $validator->errors();
        if (!$errors->isEmpty()) {
            $message = current(current($errors));

            return $message;
        }

        // 规则信息的验证
        $messages = $this->transferRulesArrVerify($requestDataArr);
        if (count($messages)) {
            return $messages;
        }

        return [];
    }

    /**
     * 商贸公司修改时的数据验证
     *
     * @param array $requestDataArr 需验证的规则
     *
     * @return array
     */
    private function tradeUpdateVerify($requestDataArr)
    {
        // 基本结构数据验证
        $messages = $this->tradeBasicDataArrVerify($requestDataArr);
        if (count($messages)) {
            return $messages;
        }

        // 公司账户信息验证
        $validator = Validator::make(
            $requestDataArr['trade_infos'],
            [
                'trade_id'       => 'required|integer|between:1,99999999',
                'login_name'     => 'required|mobile',
                'login_password' => 'string|between:6,16',
            ],
            [
                'trade_id.required' => '公司ID必须有',
                'trade_id.integer'  => '公司ID必须是一个整型',
                'trade_id.between'  => '公司ID必须是:min, 到:max的整数',

                'login_name.required' => '登录账号名必须有',
                'login_name.mobile'   => '登录账号名必须是一个手机号',

                'login_password.string'  => '账号登录密码必须是一个字符串',
                'login_password.between' => '账号登录密码长度必须在:min, 到:max之间',
            ]
        );
        $errors = $validator->errors();
        if (!$errors->isEmpty()) {
            $message = current(current($errors));

            return $message;
        }

        // 规则信息的验证
        $messages = $this->transferRulesArrVerify($requestDataArr);
        if (count($messages)) {
            return $messages;
        }

        return [];
    }

    /**
     * 商贸公司提交信息基本结构数据验证
     *
     * @param array $requestDataArr 需验证的规则
     *
     * @return array
     */
    private function tradeBasicDataArrVerify($requestDataArr)
    {
        // 基本结构数据验证
        $validator = Validator::make(
            $requestDataArr,
            [
                'trade_infos'    => 'required|array|between:3,99',
                'transfer_rules' => 'required|array|between:1,99',
            ],
            [
                'trade_infos.required' => '账户信息必须有',
                'trade_infos.array'    => '账户信息必须是一个JSON串',
                'trade_infos.between'  => '账户信息元素必须在:min, 到:max间',

                'transfer_rules.required' => '规则信息必须有',
                'transfer_rules.array'    => '规则信息必须是一个JSON串',
                'transfer_rules.between'  => '规则信息元素必须在:min, 到:max间',
            ]
        );
        $errors = $validator->errors();
        if (!$errors->isEmpty()) {
            $message = current(current($errors));

            return $message;
        }

        return [];
    }

    /**
     * 商贸公司提交信息转接规则类数据验证
     *
     * @param array $requestDataArr 需验证的数据
     *
     * @return array
     */
    private function transferRulesArrVerify($requestDataArr)
    {
        // 规则信息的验证
        $key = 1;
        foreach ($requestDataArr['transfer_rules'] as $value) {
            $validator = Validator::make(
                $value,
                [
                    'province_id'                => 'required|integer|between:-1,40',
                    'city_id'                    => 'required|integer|between:-1,500',
                    'county_id'                  => 'required|integer|between:-1,500',
                    'shop_types'                 => 'required|string|between:1,255',
                    'free_freight_order_time'    => 'required|integer|between:0,99',
                    'free_freight_max_amount'    => 'required|integer|between:0,10000000',
                    'payment_after_arrival_time' => 'required|integer|between:0,99',
                    'abort_time'                 => 'required|date_format:H:i:s',
                    'delivery_date_rule'         => 'required|integer|between:0,99',
                    'rest_day'                   => 'string|between:1,11',
                    'delivery_time'              => 'required|string|between:1,20',
                    'fees_rules'                 => 'required|array|between:1,99',
                ],
                [
                    'province_id.required' => $key . '-省ID必须有',
                    'province_id.integer'  => $key . '-省ID必须是一个整型',
                    'province_id.between'  => $key . '-省ID必须是:min, 到:max的整数',

                    'city_id.required' => $key . '-市ID必须有',
                    'city_id.integer'  => $key . '-市ID必须是一个整型',
                    'city_id.between'  => $key . '-市ID必须是:min, 到:max的整数',

                    'county_id.required' => $key . '-县ID必须有',
                    'county_id.integer'  => $key . '-县ID必须是一个整型',
                    'county_id.between'  => $key . '-县ID必须是:min, 到:max的整数',

                    'shop_types.required' => $key . '-店铺类型必须有',
                    'shop_types.string'   => $key . '-店铺类型必须是一个字符串',
                    'shop_types.between'  => $key . '-店铺类型长度必须在:min, 到:max之间',

                    'free_freight_order_time.required' => $key . '-免运费单数必须有',
                    'free_freight_order_time.integer'  => $key . '-免运费单数必须是一个整型',
                    'free_freight_order_time.between'  => $key . '-免运费单数必须是:min, 到:max的整数',

                    'free_freight_max_amount.required' => $key . '-免运费最高金额必须有',
                    'free_freight_max_amount.integer'  => $key . '-免运费最高金额必须是一个整型',
                    'free_freight_max_amount.between'  => $key . '-免运费最高金额必须是:min, 到:max的整数',

                    'payment_after_arrival_time.required' => $key . '-可货到付款单数必须有',
                    'payment_after_arrival_time.integer'  => $key . '-可货到付款单数必须是一个整型',
                    'payment_after_arrival_time.between'  => $key . '-可货到付款单数必须是:min, 到:max的整数',

                    'abort_time.required'    => $key . '-截单时间必须有',
                    'abort_time.date_format' => $key . '-截单时间必须是一个8位的完整时间格式(H:i:s)',

                    'delivery_date_rule.required' => $key . '-送达规则天数必须有',
                    'delivery_date_rule.integer'  => $key . '-送达规则天数必须是一个整型',
                    'delivery_date_rule.between'  => $key . '-送达规则天数必须是:min, 到:max的整数',

                    'rest_day.string'  => $key . '-休息日必须是一个字符串',
                    'rest_day.between' => $key . '-休息日字符长度必须在:min, 到:max之间',

                    'delivery_time.required' => $key . '-送达时间必须有',
                    'delivery_time.string'   => $key . '-送达时间必须是一个字符串',
                    'delivery_time.between'  => $key . '-送达时间长度必须在:min, 到:max之间',

                    'fees_rules.required' => $key . '-配送费用规则信息必须有',
                    'fees_rules.array'    => $key . '-配送费用规则必须是一个JSON串',
                    'fees_rules.between'  => $key . '-配送费用规则元素必须在:min, 到:max间',
                ]
            );
            $errors = $validator->errors();
            if (!$errors->isEmpty()) {
                $message = current(current($errors));

                return $message;
            }
            // 运费规则的验证
            $rulesKey = 1;
            foreach ($value['fees_rules'] as $rulesValue) {
                $rulesValidator = Validator::make(
                    $rulesValue,
                    [
                        'from_min_amount' => 'required|integer|between:0,10000000',
                        'to_max_amount'   => 'required|integer|between:0,10000000',
                        'freight_amount'  => 'required|integer|between:0,1000000',
                    ],
                    [
                        'from_min_amount.required' => $key . '-' . $rulesKey . '-单件起始金额必须有',
                        'from_min_amount.integer'  => $key . '-' . $rulesKey . '-单件起始金额必须是一个整型',
                        'from_min_amount.between'  => $key . '-' . $rulesKey . '-单件起始金额必须是:min, 到:max的整数',

                        'to_max_amount.required' => $key . '-' . $rulesKey . '-单件截止金额必须有',
                        'to_max_amount.integer'  => $key . '-' . $rulesKey . '-单件截止金额必须是一个整型',
                        'to_max_amount.between'  => $key . '-' . $rulesKey . '-单件截止金额必须是:min, 到:max的整数',

                        'freight_amount.required' => $key . '-' . $rulesKey . '-运费必须有',
                        'freight_amount.integer'  => $key . '-' . $rulesKey . '-运费必须是一个整型',
                        'freight_amount.between'  => $key . '-' . $rulesKey . '-运费必须是:min, 到:max的整数',
                    ]
                );
                $errors = $rulesValidator->errors();
                if (!$errors->isEmpty()) {
                    $message = current(current($errors));

                    return $message;
                }
                $rulesKey++;
            }
            $key++;
        }

        return [];
    }
}