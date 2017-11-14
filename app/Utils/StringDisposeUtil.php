<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/13
 * Time: 11:25
 */

namespace App\Utils;


class StringDisposeUtil
{
    /**
     * 替换所有的英文符号为中文全角
     *
     * @param $text
     *
     * @return mixed
     */
    public static function englishSymbolsConversion($text)
    {
        if (empty($text)) {
            return $text;
        }
        $text = str_replace(',', '，', $text);
        $text = str_replace(';', '；', $text);
        $text = preg_replace('/"([^"]*)"/', '“${1}”', $text);
        $text = preg_replace("/([^']*)'/", '‘${1}’', $text);
        $text = str_replace('"', '', $text);
        $text = str_replace('\'', '', $text);

        return $text;
    }
}
