<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/26
 * Time: 14:03
 */

namespace spec\App\Utils;

use PhpSpec\Laravel\LaravelObjectBehavior;
use \PHPUnit_Framework_Assert as Assert;

use App\Utils\GenerateRandomNumber;

class GenerateRandomNumberSpec extends LaravelObjectBehavior
{
    public function it_generate_random_string()
    {
        Assert::assertEquals(8, strlen(GenerateRandomNumber::generateString(8)));
        Assert::assertEquals(6, strlen(GenerateRandomNumber::generateString(6, 'abcdefghi1234567890')));
        Assert::assertEquals('', GenerateRandomNumber::generateString(0));
    }
}