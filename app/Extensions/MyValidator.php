<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/29
 * Time: 14:14
 *
 * @desc 扩展验证类
 */

namespace App\Extensions;

use Countable;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\File\File;

class MyValidator extends Validator
{
    /**
     * 手机号的验证
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     */
    public static function validateMobile($attribute, $value, $parameters)
    {
        if (preg_match("/^1[34578]{1}\d{9}$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断值是否为空(但不包含空串)
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     *
     * @return bool
     */
    public static function validateRequiredNull($attribute, $value, $parameters)
    {
        if (is_null($value)) {
            return false;
        } elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
            return false;
        } elseif ($value instanceof File) {
            return (string)$value->getPath() != '';
        }

        return true;
    }

    /**
     * 判断数组中的指定键名是否存在
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @param object $validator
     *
     * @return bool
     */
    public static function validateArrHasKey($attribute, $value, $parameters, $validator)
    {
        $valueArr = array_keys($value);
        $diffArr = array_diff($parameters, $valueArr);
        if (count($diffArr)) {
            foreach ($diffArr as $val) {
                $validator->errors()->add($attribute, "{$attribute}中缺少‘{$val}’参数");

                return false;
            }
        }

        return true;
    }

    /**
     * 验证输入值不可小于某个验证值
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     */
    public static function validateGreaterThan($attribute, $value, $parameters)
    {
        $valueType = gettype($value);
        switch ($valueType) {
            case 'string':
                if (is_numeric($value)) {
                    $size = $value;
                } else {
                    $size = strlen($value);
                }
                break;
            case 'integer':
            case 'double':
                $size = $value;
                break;
            default:
                return false;
        }
        if ($size <= $parameters[0]) {
            return false;
        }

        return true;
    }
}
