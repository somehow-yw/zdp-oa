<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/12
 * Time: 18:21
 */

namespace App\Repositories\Version\Contracts;

/**
 * Interface VersionLogRepository.
 * 版本日志数据管理
 * @package app\Repositories\Version
 */
interface VersionLogRepository
{
    /**
     * 添加版本日志信息
     *
     * @param $bigVersion     int 大版本号
     * @param $smallVersion   int 小版本号
     * @param $developVersion int 开发版本号
     * @param $remark         string 备注信息
     *
     * @return object Eloquent ORM Collect
     */
    public function addVersion($bigVersion, $smallVersion, $developVersion, $remark);

    /**
     * 版本日志列表
     *
     * @param       $size       int 获取数据量
     * @param       $bigVersion int 大版本号 可能为0 0=不判断
     * @param array $selectArr  array 获取字段列 格式：['select1', 'select N']
     *
     * @return object Eloquent ORM Collect
     */
    public function getVersionList($size, $bigVersion, $selectArr);

    /**
     * 修改版本日志信息
     *
     * @param $developVersion int 开发版本号
     * @param $remark         string 备注信息
     *
     * @return object Eloquent ORM Collect
     */
    public function updateVersion($developVersion, $remark);
}
