<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/14/16
 * Time: 2:33 PM
 */

use Carbon\Carbon;

if (!function_exists('time_now')) {
    /**
     * 取得当前日期时间
     * @return string
     */
    function time_now()
    {
        return Carbon::now()->format('Y-m-d H:i:s');
    }
}

if (!function_exists('fileLogWrite')) {
    /**
     * 记录信息到指定的文件
     *
     * @param        $messages
     * @param string $logPath
     */
    function fileLogWrite($messages, $logPath)
    {
        $dirPath = dirname($logPath);
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
        $date = date('Y-m-d H:i:s');
        $lineFeed = PHP_EOL;
        $messages = "{$lineFeed}[{$date}]-----{$lineFeed}{$messages}";
        file_put_contents($logPath, $messages, FILE_APPEND);
    }
}
