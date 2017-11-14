<?php

namespace App\Utils;

class MoneyUnitConvertUtil
{
    /**
     * Convert money unit from yuan to fen,
     *
     * @param float|string $yuan
     *
     * @return int fen
     */
    public static function yuanToFen($yuan)
    {
        $fenStr = (string)($yuan * 100);
        if ($pointIndex = strpos($fenStr, '.')) {
            return (int)substr($fenStr, 0, strpos($fenStr, '.'));
        } else {
            return (int)$fenStr;
        }
    }

    /**
     * Convert money unit from fen to yuan
     *
     * @param int $fen
     *
     * @return string yuan
     */
    public static function fenToYuan($fen)
    {
        return sprintf('%.2f', $fen / 100);
    }

    /**
     * 删除小数点后面多余的零 如：2.00->2; 2.10->2.1; 2.01->2.01
     *
     * @param int|string $number
     *
     * @return double
     */
    public static function delNumberPointZero($number)
    {
        $number = trim(strval($number));
        $returnNumber = $number;
        if (preg_match('#^-?\d+?\.0+$#', $number)) {
            $returnNumber = preg_replace('#^(-?\d+?)\.0+$#', '$1', $number);
        }
        if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $number)) {
            $returnNumber = preg_replace('#^(-?\d+\.[0-9]+?)0+$#', '$1', $number);
        }

        return (double)$returnNumber;
    }
}
