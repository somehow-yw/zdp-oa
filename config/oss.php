<?php
/**
 * Created by PhpStorm.
 * OSS相关的配置，除了签名串（签名串在signature中配置）
 * User: fer
 * Date: 2016/9/5
 * Time: 12:15
 */
date_default_timezone_set('PRC');

$dateTxt = date('Y-m-d H:i:s', time());
return [
    /*
    |--------------------------------------------------------------------------
    | OSS服务器接口配置
    |--------------------------------------------------------------------------
    */
    // OSS BUCKET
    'bucket'          => 'idongpin',
    // ID 服务器地址
    'oss_cons_info'   => [
        'access_id'  => 'TJRpuASj4TrZ1MPd',
        'access_key' => 'JAeF8EKZ1m6IqDc9d3lycWs4kwqgcX',
        'hostname'   => env('OSS_URL', 'oss-cn-qingdao-internal.aliyuncs.com'),
    ],
    // OSS上传单个图片时的基本头信息
    'options'         => [
        'headers' => [
            'Content-Encoding'    => 'utf-8',
        ],
    ],
    // 图片水印
    'oss_watermark'   => env('WATERMARK'),
    // 上传目录
    'oss_object_path' => env('OSS_OBJECT_PATH'),
    // 图片获取的URL
    'oss_images_url'  => 'http://img.idongpin.com',
    'object_path'     => env('OSS_OBJECT_PATH'),
    'read_file_path'  => env('READ_FILE_PATH'),
    'watermark'       => env('WATERMARK'),
    'oss_bucket'      => env('OSS_BUCKET'),
    'signTime'        => $dateTxt,
];