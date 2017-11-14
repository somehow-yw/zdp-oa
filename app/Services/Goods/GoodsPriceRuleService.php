<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/11
 * Time: 20:10
 */

namespace App\Services\Goods;

use App\Models\DpPriceRule;

/**
 * Class GoodsPriceRuleService.
 * 价格体系
 *
 * @package App\Services\Seller\Goods
 */
class GoodsPriceRuleService
{
    /**
     * 获取价格体系规则
     *
     * @return array
     */
    public function getPriceRule()
    {
        $priceRuleArr = DpPriceRule::$priceRuleArrForDisplay;
        unset($priceRuleArr[0]);
        array_multisort($priceRuleArr);

        return ['price_rules' => $priceRuleArr];
    }
}
