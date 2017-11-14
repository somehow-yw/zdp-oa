<?php

/*
 | 手机短信相关配置
 */

return [

    // 日志文件路径 仅用于阿里云通信
    'log_path' => storage_path(env('MOBILE_SMS_LOG_PATH', 'logs/sms/')),

    // 验证码
    'verify'   => [

        // 同一手机两次发送间隔, 秒
        'sleep'   => env('MOBILE_VERIFY_SLEEP', 30),

        // 过期时间, 分
        'expired' => env('MOBILE_VERIFY_EXPIRED', 5),

    ],

    // 短信
    'sms'      => [

        // 是否为测试模式
        'debug'         => env('MOBILE_SMS_DEBUG', true),

        // 云通讯短信接口账号
        'sid'           => env('MOBILE_SMS_SID',
            '8a48b5514fd49643014fda4b680211eb'),

        // 云通讯短信接口账号 token
        'token'         => env('MOBILE_SMS_TOKEN',
            '77d5151501614d46a52e1f4c3c7d258b'),

        // 云通讯短信接口账号 appid
        'appid'         => env('MOBILE_SMS_APPID',
            '8a48b5514fd49643014fda4e9b871200'),

        // 阿里云通信 app key
        'app_key'       => env('MOBILE_SMS_APPKEY', '23576007'),

        // 阿里云通信 app secret
        'app_secret'    => env('MOBILE_SMS_APP_SECRET',
            '87eb13d880d220a267e29ac15b0fe112'),

        // 阿里云通信 sign name
        'app_sign_name' => env('MOBILE_SMS_APP_SIGN_NAME', '冰河物流'),

        // 短信日志文件
        'log'           => env('MOBILE_SMS_LOG', 'logs/sms.log'),

        // 线上模式中是否开启短信日志
        'enable_log'    => env('MOBILE_SMS_ENABLE_LOG', true),

        // 模板短信配置
        'template'      => [

            // 验证码模板ID
            'verify'        => env('MOBILE_SMS_TMEPLATE_VERIFY',
                'SMS_13225385'),

            // 运单发货通知
            'delivery_send' => env(
                'MOBILE_SMS_TEMPLATE_DELIVERY_SEND',
                'SMS_77325086'
            ),

        ],

        // 使用 SDK
        'sdk'           => env('MOBILE_SMS_SDK', 'aliyun'),

    ],

    'connection' => env('MOBILE_CONNECTION', 'mysql_logistics'),

];
