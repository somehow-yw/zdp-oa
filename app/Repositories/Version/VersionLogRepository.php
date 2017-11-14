<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/12
 * Time: 18:21
 */

namespace App\Repositories\Version;

use App\Repositories\Version\Contracts\VersionLogRepository as RepositoriesContract;

use App\Models\DpVersionManageLog;

use App\Exceptions\System\VersionException;

/**
 * Interface VersionLogRepository.
 * 版本日志数据管理
 * @package app\Repositories\Version
 */
class VersionLogRepository implements RepositoriesContract
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
    public function addVersion($bigVersion, $smallVersion, $developVersion, $remark)
    {
        $addArr = [
            'big_version'     => $bigVersion,
            'small_version'   => $smallVersion,
            'develop_version' => $developVersion,
            'remark'          => $remark,
        ];

        return DpVersionManageLog::create($addArr);
    }

    /**
     * 版本日志列表
     *
     * @param       $size       int 获取数据量
     * @param       $bigVersion int 大版本号 可能为0 0=不判断
     * @param array $selectArr  array 获取字段列 格式：['select1', 'select N']
     *
     * @return object Eloquent ORM Collect
     */
    public function getVersionList($size, $bigVersion, $selectArr)
    {
        $query = DpVersionManageLog::select($selectArr)
            ->orderBy('id', 'desc');
        if (!empty($bigVersion)) {
            $query = $query->where('big_version', $bigVersion);
        }

        return $query->paginate($size);
    }

    /**
     * 修改版本日志信息
     *
     * @param $developVersion int 开发版本号
     * @param $remark         string 备注信息
     *
     * @return object Eloquent ORM Collect
     * @throws VersionException
     */
    public function updateVersion($developVersion, $remark)
    {
        $versionInfoCollect = DpVersionManageLog::getNewVersionInfo();
        // 可修改成的版本号(原版本号加1)
        $updateDevelopVersion = $versionInfoCollect->develop_version + 1;
        if ($updateDevelopVersion != $developVersion) {
            throw new VersionException(VersionException::DEVELOP_VERSION_UPDATE_ERROR);
        }
        $versionInfoCollect->develop_version = $developVersion;
        $versionInfoCollect->remark = $remark;
        $versionInfoCollect->save();

        return $versionInfoCollect;
    }
}
