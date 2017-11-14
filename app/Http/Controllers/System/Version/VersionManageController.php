<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/12/12
 * Time: 17:55
 */

namespace App\Http\Controllers\System\Version;

use Illuminate\Http\Request;
use App\Services\System\Version\VersionManageService;
use App\Http\Controllers\Controller;

/**
 * Class VersionManageController.
 * 版本日志管理
 * @package App\Http\Controllers\System\Version
 */
class VersionManageController extends Controller
{
    /**
     * 添加版本日志信息
     *
     * @param Request              $request
     * @param VersionManageService $versionManageService
     *
     * @return \Illuminate\Http\Response
     */
    public function addVersion(Request $request, VersionManageService $versionManageService)
    {
        $this->validate(
            $request,
            [
                'big_version'     => 'required|integer|between:1,100',
                'small_version'   => 'required|integer|between:1,100',
                'develop_version' => 'required|integer|between:1,100',
                'remark'          => 'required|string|between:1,250',
            ],
            [
                'big_version.required' => '大版本号必须有',
                'big_version.integer'  => '大版本号必须是整型',
                'big_version.between'  => '大版本号应在:min到:max',

                'small_version.required' => '小版本号必须有',
                'small_version.integer'  => '小版本号必须是整型',
                'small_version.between'  => '小版本号应在:min到:max',

                'develop_version.required' => '开发版本号必须有',
                'develop_version.integer'  => '开发版本号必须是整型',
                'develop_version.between'  => '开发版本号应在:min到:max',

                'remark.required' => '备注信息必须有',
                'remark.integer'  => '备注信息必须是字符串',
                'remark.between'  => '备注信息长度应在:min到:max',
            ]
        );

        $versionManageService->addVersion(
            $request->input('big_version'),
            $request->input('small_version'),
            $request->input('develop_version'),
            $request->input('remark')
        );

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $this->render(
            'system.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 版本日志列表
     *
     * @param Request              $request
     * @param VersionManageService $versionManageService
     *
     * @return \Illuminate\Http\Response
     */
    public function getVersionList(Request $request, VersionManageService $versionManageService)
    {
        $this->validate(
            $request,
            [
                'page'        => 'required|integer|min:1',
                'size'        => 'required|integer|between:1,100',
                'big_version' => 'integer|between:1,100',
            ],
            [
                'page.required' => '请求页数必须有',
                'page.integer'  => '请求页数必须是整型',
                'page.min'      => '请求页数不可小于:min',

                'size.required' => '请求数据量必须有',
                'size.integer'  => '请求数据量必须是整型',
                'size.between'  => '请求数据量应在:min到:max',

                'big_version.integer' => '大版本号必须是整型',
                'big_version.between' => '大版本号应在:min到:max',
            ]
        );

        $listArr = $versionManageService->getVersionList(
            $request->input('page'),
            $request->input('size'),
            $request->input('big_version', 0)
        );

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listArr,
        ];

        return $this->render(
            'system.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 修改版本信息
     *
     * @param Request              $request
     * @param VersionManageService $versionManageService
     *
     * @return \Illuminate\Http\Response
     */
    public function updateVersion(Request $request, VersionManageService $versionManageService)
    {
        $this->validate(
            $request,
            [
                'develop_version' => 'required|integer|between:1,100',
                'remark'          => 'required|string|between:1,250',
            ],
            [
                'develop_version.required' => '开发版本号必须有',
                'develop_version.integer'  => '开发版本号必须是整型',
                'develop_version.between'  => '开发版本号应在:min到:max',

                'remark.required' => '备注信息必须有',
                'remark.integer'  => '备注信息必须是字符串',
                'remark.between'  => '备注信息长度应在:min到:max',
            ]
        );

        $versionManageService->updateVersion($request->input('develop_version'), $request->input('remark'));

        $reData = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return $this->render(
            'system.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}
