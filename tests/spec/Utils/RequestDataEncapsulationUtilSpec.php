<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/26
 * Time: 15:32
 */

namespace spec\App\Utils;

use PhpSpec\Laravel\LaravelObjectBehavior;

use App\Utils\RequestDataEncapsulationUtil;

class RequestDataEncapsulationUtilSpec extends LaravelObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf(RequestDataEncapsulationUtil::class);
    }

    /**
     * 签名测试
     */
    public function it_request_data_sign()
    {
        $requestArr = [
            'key'   => 333,
            'value' => '测试',
        ];
        $signKey = config('signature.trade_sign_key');
        $this->RequestDataSign($requestArr, $signKey)
            ->shouldHaveCount(4);
        $this->RequestDataSign($requestArr, $signKey)
            ->shouldHaveKey('signature');
        $this->RequestDataSign($requestArr, $signKey)
            ->shouldHaveKeyWithValue('key', 333);
    }
}