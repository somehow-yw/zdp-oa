<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-1-23
 * Time: 上午10:59
 */

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Services\Goods\GoodsPriceRuleService;

class GoodsPriceRuleController extends Controller
{
    /**
     * 获取价格体系规则
     *
     * @param GoodsPriceRuleService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getPriceRule(GoodsPriceRuleService $service)
    {
        $priceRuleArr = $service->getPriceRule();
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $priceRuleArr,
        ];

        return $this->render(
            'goods.list',
            $reDataArr['data'],
            $reDataArr['message'],
            $reDataArr['code']
        );
    }
}