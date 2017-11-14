<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/14
 * Time: 17:54
 */

namespace App\Services\Goods\Traits;

/**
 * Class AttrConverTrait.
 * 将属性JSON结构转成字符串的形式来表现值
 *
 * @package App\Services\Goods\Traits
 */
trait AttrConvertTrait
{
    /**
     * 将属性JSON结构转成字符串的形式来表现值
     *
     * @param $attrArr          array 需处理的属性数组
     *                          [
     *                          ["value":3, "unit":"袋"]
     *                          ]
     * @param $constraintTypeId int 约束的类型
     *
     * @return string
     */
    public function attrArrToText($attrArr, $constraintTypeId)
    {
        $formatArr = config('input_format.goods_attribute');
        $formatCollect = collect($formatArr);
        $formatCollect = $formatCollect->keyBy('id');
        $formatArr = $formatCollect->all();
        $attrText = '';
        if (!array_key_exists($constraintTypeId, $formatArr) || empty($attrArr[0]['value'])) {
            return $attrText;
        }
        switch ($constraintTypeId) {
            // 文本框
            case 1:
                $attrText = $this->textConvert($attrArr);
                break;
            // 单选
            case 2:
                $attrText = $this->radioConvert($attrArr);
                break;
            // 多选
            case 3:
                $attrText = $this->checkboxConvert($attrArr);
                break;
            // X-Y区间
            case 4:
                $attrText = $this->xyBetweenConvert($attrArr);
                break;
            // X*Y值
            case 5:
                $attrText = $this->xyMultiplyConvert($attrArr);
                break;
        }

        return $attrText;
    }

    /**
     * 文本框类型的转换
     *
     * @param $attrArr
     *
     * @return string
     */
    private function textConvert($attrArr)
    {
        $value = $attrArr[0]['value'];
        $unit = isset($attrArr[0]['unit']) ? $attrArr[0]['unit'] : '';
        $text = $value . $unit;

        return $text;
    }

    /**
     * 单选类型的转换
     *
     * @param $attrArr
     *
     * @return string
     */
    private function radioConvert($attrArr)
    {
        $value = $attrArr[0]['value'];
        $unit = empty($attrArr[0]['unit']) ? '' : $attrArr[0]['unit'];
        $text = $value . $unit;

        return $text;
    }

    /**
     * 多选类型的转换
     *
     * @param $attrArr
     *
     * @return string
     */
    private function checkboxConvert($attrArr)
    {
        $text = '';
        foreach ($attrArr as $key => $attr) {
            $value = $attr['value'];
            $unit = empty($attr['unit']) ? '' : $attr['unit'];
            if ($key) {
                $text .= ',' . $value . $unit;
            } else {
                $text .= $value . $unit;
            }
        }

        return $text;
    }

    /**
     * X-Y区间类型的转换
     *
     * @param $attrArr
     *
     * @return string
     */
    private function xyBetweenConvert($attrArr)
    {
        $text = '';
        foreach ($attrArr as $key => $attr) {
            $value = $attr['value'];
            $unit = empty($attr['unit']) ? '' : $attr['unit'];
            if ($key) {
                $text .= $value . $unit;
            } else {
                $text .= $value . $unit . '-';
            }
        }

        return $text;
    }

    /**
     * X*Y类型的转换
     *
     * @param $attrArr
     *
     * @return string
     */
    private function xyMultiplyConvert($attrArr)
    {
        $text = '';
        foreach ($attrArr as $key => $attr) {
            $value = $attr['value'];
            $unit = empty($attr['unit']) ? '' : $attr['unit'];
            if ($key) {
                $text .= $value . $unit;
            } else {
                $text .= $value . $unit . '*';
            }
        }

        return $text;
    }
}
