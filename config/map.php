<?php

/*
 | 高德地图相关配置
 */

return [

    // 调试模式
    'debug' => env('MAP_DEBUG', true),

    // 高德地图应用 Key
    'amap_key' => env('MAP_AMAP_KEY', '6d23324de26e647add1cbcf7f5bc3e59'),

    // 腾讯地图应用 Key
    'tencent_key' => env(
        'MAP_TENCENT_KEY',
        'RADBZ-RNKW6-KQSSW-MC3EL-AOD66-R2BWK'
            . ',KCTBZ-NPOR5-AXNIR-Q2VNE-P3L2Q-NSBX2'
            . ',TTJBZ-AAZRV-KEAPC-UMQJW-RVPGZ-E3B3Z'
    ),

    // 接口通信格式
    'output' => env('MAP_FORMAT', 'JSON'),

    // 日志文件
    'log' => env('MAP_LOG', 'logs/map.log'),

    // 使用SDK
    'sdk' => env('MAP_SDK', 'tencent'),

    'connection' => env('MAP_CONNECTION', 'mysql_logistics'),

];
