<?php

return [
    'debug' => env('APP_DEBUG', true),

    'connection' => env('SERVICE_PROVIDER_CONNECTION', 'mysql_service_provider'), // 默认数据库连接
];