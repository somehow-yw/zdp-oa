<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/3/8
 * Time: 13:57
 */

namespace App\Utils;

class GenerateRandomNumber
{
    /**
     * 生成随机字符串
     *
     * @param int    $length 随机串的长度
     * @param string $chars  随机串生成时的字符集
     * @return string
     */
    public static function generateString($length = 8, $chars = '')
    {
        // 字符集，可任意添加你需要的字符
        $chars = empty($chars) ? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ' : $chars;

        $number = '';
        for ($i = 0; $i < $length; $i++) {
            $number .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $number;
    }
}
