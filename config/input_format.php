<?php
/**
 * Created by PhpStorm.
 * 可输入格式验证配置
 * User: fer
 * Date: 2016/9/27
 * Time: 16:50
 */

return [
    // 商品属性格式
    'goods_attribute' => [
        [
            'id'       => 2,
            'format'   => 'radio',
            'name'     => '单选',
            'validate' => 'between:1,10',
        ],
        [
            'id'       => 3,
            'format'   => 'checkbox',
            'name'     => '多选',
            'validate' => 'between:1,10',
        ],
        [
            'id'       => 1,
            'format'   => 'text',
            'name'     => '文本框',
            'validate' => 'size:1',
        ],
        [
            'id'       => 4,
            'format'   => 'between',
            'name'     => 'X-Y区间',
            'validate' => 'size:2',
        ],
        [
            'id'       => 5,
            'format'   => 'related',
            'name'     => 'X*Y值',
            'validate' => 'size:2',
        ],
    ],

    // 值的验证格式
    'verify_format'   => [
        [
            'rule' => 'string',
            'name' => '字符串',
        ],
        [
            'rule' => 'integer',
            'name' => '整数',
        ],
        [
            'rule' => 'numeric',
            'name' => '数值',
        ],
    ],
];