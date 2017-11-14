<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/12
 * Time: 14:32
 */

namespace App\Services\System\Version;

use App\Models\DpVersionManageLog;

use App\Repositories\Version\Contracts\VersionLogRepository;

/**
 * Class VersionManage.
 * 版本日志管理
 * @package App\Services\System\Version
 */
class VersionManageService
{
    private $versionLogRepo;

    public function __construct(VersionLogRepository $versionLogRepo)
    {
        $this->versionLogRepo = $versionLogRepo;
    }

    /**
     * 返回最新的版本信息
     * @return string
     */
    public function getNewVersionInfo()
    {
        $version = '3.1.1';
        $versionInfoCollect = DpVersionManageLog::getNewVersionInfo();

        if (!is_null($versionInfoCollect)) {
            $version = "{$versionInfoCollect->big_version}." .
                       "{$versionInfoCollect->small_version}.{$versionInfoCollect->develop_version}";
        }

        return $version;
    }

    /**
     * 添加版本日志信息
     *
     * @param $bigVersion     int 大版本号
     * @param $smallVersion   int 小版本号
     * @param $developVersion int 开发版本号
     * @param $remark         string 备注信息
     *
     * @return array
     */
    public function addVersion($bigVersion, $smallVersion, $developVersion, $remark)
    {
        $versionCollect = $this->versionLogRepo->addVersion($bigVersion, $smallVersion, $developVersion, $remark);

        return $versionCollect->toArray();
    }

    /**
     * 版本日志列表
     *
     * @param $page        int 获取页数
     * @param $size        int 获取数据量
     * @param $bigVersion  int 大版本号 可为0 0=不限制
     *
     * @return array
     */
    public function getVersionList($page, $size, $bigVersion)
    {
        $selectArr = ['*'];
        $versionCollects = $this->versionLogRepo->getVersionList($size, $bigVersion, $selectArr);

        $reDataArr = [
            'page'         => (int)$page,
            'total'        => $versionCollects->total(),
            'version_logs' => [],
        ];
        if ($versionCollects->count()) {
            $versionArrs = $versionCollects->toArray();
            $reDataArr['version_logs'] = $versionArrs['data'];
        }

        return $reDataArr;
    }

    /**
     * 版本日志修改
     *
     * @param $developVersion int 开发版本号
     * @param $remark         string 备注信息
     *
     * @return object
     */
    public function updateVersion($developVersion, $remark)
    {
        $newVersionCollect = $this->versionLogRepo->updateVersion($developVersion, $remark);

        return $newVersionCollect;
    }
}
