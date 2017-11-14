<?php
/**
 * Created by PhpStorm.
 * 权限管理.
 *
 * User: xty
 * Date: 2016/6/22
 * Time: 11:44
 */

namespace App\Services;

use App\Repositories\Contracts\PrivilegeRepository;

use App\Models\ActionPrivilege;
use App\Models\User;

use App\Exceptions\AppException;
use App\Exceptions\Privilege\PrivilegeExceptionCode;

class PrivilegeService
{
    private $privilegeRepo;

    public function __construct(
        PrivilegeRepository $privilegeRepo
    ) {
        $this->privilegeRepo = $privilegeRepo;
    }

    /**
     * 添加权限
     *
     * @param int    $parentId      父级ID
     * @param string $privilegeName 权限名称
     * @param string $privilegeTag  权限标记
     * @param int    $navigateRank  权限级别
     * @param string $routeTxt      操作路由
     * @param string $remark        权限说明
     *
     * @throws AppException
     * @return array
     */
    public function addPrivilege(
        $parentId,
        $privilegeName,
        $privilegeTag,
        $navigateRank,
        $routeTxt,
        $remark
    ) {
        if ($navigateRank == 1) {
            // 如果权限级别为一级，则父级ID为0，节点也就为0
            $nodes = $parentId;
        } else {
            // 取得父级权限的信息
            $privilegeInfoObj = $this->privilegeRepo->getPrivilegeById($parentId);
            if ( ! $privilegeInfoObj) {
                throw new AppException('父级权限不存在', PrivilegeExceptionCode::PARENT_PRIVILEGE_NULL);
            }
            if (($navigateRank == 0 && $privilegeInfoObj->navigate_rank <= $navigateRank)
                || ($navigateRank != 0 && $privilegeInfoObj->navigate_rank >= $navigateRank)
            ) {
                throw new AppException('与父级权限级别不符合', PrivilegeExceptionCode::PRIVILEGE_RANK_ERROR);
            }
            $nodes = $privilegeInfoObj->nodes . ',' . $parentId;
        }

        // 检查权限标记是否已存在
        $privilegeInfoObj = $this->privilegeRepo->getPrivilegeByTag($privilegeTag);
        if ($privilegeInfoObj) {
            throw new AppException('权限标记已经存在', PrivilegeExceptionCode::PRIVILEGE_TAG_EXISTING);
        }

        $routeTxt = str_replace('\\', '/', $routeTxt);
        $reData = $this->privilegeRepo->addPrivilege(
            $parentId,
            $nodes,
            $privilegeName,
            $privilegeTag,
            $navigateRank,
            $routeTxt,
            $remark
        );

        if ( ! $reData) {
            throw new AppException('权限写入失败', PrivilegeExceptionCode::WRITE_PRIVILEGE_FAILURE);
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 权限列表
     *
     * @return array
     */
    public function getPrivilegeList()
    {
        $privilegeStatusArr = [
            ActionPrivilege::NORMAL_STATUS,
            ActionPrivilege::CLOSE_STATUS,
        ];
        $privilegeInfoObj = $this->privilegeRepo->getPrivilegeList($privilegeStatusArr);
        $rePrivilegeArr = $this->privilegeTree($privilegeInfoObj);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $rePrivilegeArr,
        ];
    }

    /**
     * 权限状态修改
     *
     * @param int $id     权限ID
     * @param int $status 待修改的状态
     *
     * @return array
     * @throws AppException
     */
    public function updatePrivilegeStatus($id, $status)
    {
        $statusArr = [ActionPrivilege::NORMAL_STATUS, ActionPrivilege::CLOSE_STATUS];
        $privilegeInfoObj = $this->privilegeRepo->getPrivilegeById($id, $statusArr);
        if ( ! $privilegeInfoObj) {
            throw new AppException('权限不存在', PrivilegeExceptionCode::PRIVILEGE_NULL);
        }
        if ($privilegeInfoObj->status == $status) {
            throw new AppException(
                '权限已有状态不可更改为当前状态',
                PrivilegeExceptionCode::PRIVILEGE_STATUS_CANNOT_CHANGED
            );
        }
        if ($status == ActionPrivilege::NORMAL_STATUS && $privilegeInfoObj->navigate_rank != 1) {
            // 获取其父权限信息
            $parentPrivilegeInfoObj = $this->privilegeRepo->getPrivilegeById($privilegeInfoObj->parent_id, $statusArr);
            if ($parentPrivilegeInfoObj->status == ActionPrivilege::CLOSE_STATUS) {
                throw new AppException(
                    '请先开启父权限',
                    PrivilegeExceptionCode::PRIVILEGE_STATUS_CANNOT_CHANGED
                );
            }
        }
        $this->privilegeRepo->updatePrivilegeStatus($privilegeInfoObj, $id, $status);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 权限信息获取
     *
     * @param int $id 需获取信息的ID
     *
     * @return array
     * @throws AppException
     */
    public function getPrivilegeInfo($id)
    {
        $statusArr = [ActionPrivilege::NORMAL_STATUS, ActionPrivilege::CLOSE_STATUS];
        $privilegeInfoObj = $this->privilegeRepo->getPrivilegeById($id, $statusArr);
        if ( ! $privilegeInfoObj) {
            throw new AppException('权限不存在', PrivilegeExceptionCode::PRIVILEGE_NULL);
        }

        $reDataArr = [
            'parent_id'      => $privilegeInfoObj->parent_id,
            'privilege_name' => $privilegeInfoObj->privilege_name,
            'privilege_tag'  => $privilegeInfoObj->privilege_tag,
            'navigate_rank'  => $privilegeInfoObj->navigate_rank,
            'url'            => $privilegeInfoObj->route,
            'status'         => $privilegeInfoObj->status,
            'remark'         => $privilegeInfoObj->remark,
        ];

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 权限信息修改
     *
     * @param int    $id            待修改的权限ID
     * @param string $privilegeName 权限名称
     * @param string $routeTxt      权限路由
     * @param string $remark        权限备注
     *
     * @return array
     * @throws AppException
     */
    public function updatePrivilege($id, $privilegeName, $routeTxt, $remark)
    {
        $statusArr = [ActionPrivilege::NORMAL_STATUS, ActionPrivilege::CLOSE_STATUS];
        $privilegeInfoObj = $this->privilegeRepo->getPrivilegeById($id, $statusArr);
        if ( ! $privilegeInfoObj) {
            throw new AppException('权限不存在', PrivilegeExceptionCode::PRIVILEGE_NULL);
        }

        $routeTxt = str_replace('\\', '/', $routeTxt);
        $this->privilegeRepo->updatePrivilege($privilegeInfoObj, $privilegeName, $routeTxt, $remark);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 返回当前会员的权限信息
     *
     * @param object $userInfoArr 当前会员信息数组
     *                            -- user_name 会员名称
     *                            -- id 会员ID
     *
     * @return array
     * @throws AppException
     */
    public function getUserNavigate($userInfoArr)
    {
        $notUpdateUserArr = User::superActionUsers();
        if (in_array($userInfoArr['user_name'], $notUpdateUserArr)) {
            $statusArr = [ActionPrivilege::NORMAL_STATUS];
            $userNavigateObj = $this->privilegeRepo->getPrivilegeList($statusArr);
            if ($userNavigateObj->isEmpty()) {
                throw new AppException('还未分配权限，请联系管理员', PrivilegeExceptionCode::WITHOUT_ANY_PRIVILEGE);
            }
        } else {
            $userId = $userInfoArr['id'];
            $userNavigateObj = $this->privilegeRepo->getUserNavigate($userId);
            if ( ! $userNavigateObj) {
                throw new AppException('还未分配权限，请联系管理员', PrivilegeExceptionCode::WITHOUT_ANY_PRIVILEGE);
            }
        }

        $rePrivilegeArr = $this->userPrivilegeTree($userNavigateObj);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $rePrivilegeArr,
        ];
    }

    /**
     * 返回当前会员的权限信息 根据会员ID
     *
     * @param int $userId 会员ID
     *
     * @return array
     * @throws AppException
     */
    public function getUserPrivilegeByUserId($userId)
    {
        $userNavigateObj = $this->privilegeRepo->getUserNavigate($userId);

        $rePrivilegeArr = [
            'navigates'         => '',
            'execute_privilege' => '',
        ];
        if ($userNavigateObj) {
            $rePrivilegeArr = $this->userListPrivilegeTree($userNavigateObj);
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $rePrivilegeArr,
        ];
    }

    /**
     * 返回整理后的权限数组
     *
     * @param object $privilegeInfoObj 待整理结构的权限对象
     *
     * @return array
     */
    private function privilegeTree($privilegeInfoObj)
    {
        $rePrivilegeArr = [
            'navigates'         => '',
            'execute_privilege' => '',
        ];
        if ( ! $privilegeInfoObj->isEmpty()) {
            $one_i = 0;
            $two_i = 0;
            $three_i = [];
            foreach ($privilegeInfoObj as $item) {
                if ($item->navigate_rank != 1) {
                    $nodes = substr($item->nodes,2);
                    $parentNodes = str_replace(',', '.', $nodes);
                }
                if ($item->navigate_rank == 1) {
                    $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['name'] = $item->privilege_name;
                    $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['privilege_tag'] = $item->privilege_tag;
                    $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['id'] = $item->id;
                    $one_i++;
                } elseif ($item->navigate_rank > 1) {
                    $newArr = [
                        'name'          => $item->privilege_name,
                        'privilege_tag' => $item->privilege_tag,
                        'id'            => $item->id,
                    ];
                    $arrayKeys = "navigates.{$item->navigate_rank}.{$parentNodes}";
                    $this->array_num_set($rePrivilegeArr, $arrayKeys, $newArr);
                } elseif ($item->navigate_rank == 0) {
                    $newArr = [
                        'name'          => $item->privilege_name,
                        'privilege_tag' => $item->privilege_tag,
                        'id'            => $item->id,
                    ];
                    $this->array_num_set($three_i, $parentNodes, $newArr);
                }
            }
            $rePrivilegeArr['execute_privilege'] = $three_i;
            unset($three_i);
        }

        return $rePrivilegeArr;
    }

    /**
     * 返回整理后的当前会员权限数组
     *
     * @param object $privilegeInfoObj 待整理结构的权限对象
     *
     * @return array
     */
    private function userPrivilegeTree($privilegeInfoObj)
    {
        $rePrivilegeArr = [
            'navigates'         => '',
            'execute_privilege' => '',
        ];
        $one_i = 0;
        $two_i = 0;
        $three_i = [];
        foreach ($privilegeInfoObj as $item) {
            if ($item->navigate_rank != 1) {
                $nodes = substr($item->nodes,2);
                $parentNodes = str_replace(',', '.', $nodes);
            }
            if ($item->navigate_rank == 1) {
                $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['name'] = $item->privilege_name;
                $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['privilege_tag'] = $item->privilege_tag;
                $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['url'] = $item->route;
                $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['id'] = $item->id;
                $one_i++;
            } elseif ($item->navigate_rank > 1) {
                /*$arrayKeys = "navigates.{$item->navigate_rank}.{$parentNodes}.{$two_i}";
                array_set($rePrivilegeArr, "{$arrayKeys}.name", $item->privilege_name);
                array_set($rePrivilegeArr, "{$arrayKeys}.privilege_tag", $item->privilege_tag);
                array_set($rePrivilegeArr, "{$arrayKeys}.url", $item->route);
                array_set($rePrivilegeArr, "{$arrayKeys}.id", $item->id);
                $two_i++;*/
                $newArr = [
                    'name'          => $item->privilege_name,
                    'privilege_tag' => $item->privilege_tag,
                    'url'           => $item->route,
                    'id'            => $item->id,
                ];
                $arrayKeys = "navigates.{$item->navigate_rank}.{$parentNodes}";
                $this->array_num_set($rePrivilegeArr, $arrayKeys, $newArr);
            } elseif ($item->navigate_rank == 0) {
                $newArr = [
                    'name'          => $item->privilege_name,
                    'privilege_tag' => $item->privilege_tag,
                    'url'           => $item->route,
                    'id'            => $item->id,
                ];
                $this->array_num_set($three_i, $parentNodes, $newArr);
            }
            $rePrivilegeArr['user_tags'][] = $item->privilege_tag;
        }
        $rePrivilegeArr['execute_privilege'] = $three_i;
        unset($three_i);

        return $rePrivilegeArr;
    }

    /**
     * 返回整理后的当前会员权限数组(用于会员列表中)
     *
     * @param object $privilegeInfoObj 待整理结构的权限对象
     *
     * @return array
     */
    private function userListPrivilegeTree($privilegeInfoObj)
    {
        $rePrivilegeArr = [
            'navigates'         => '',
            'execute_privilege' => '',
        ];
        if ($privilegeInfoObj) {
            $one_i = 0;
            $two_i = 0;
            $three_i = [];
            foreach ($privilegeInfoObj as $item) {
                if ($item->navigate_rank != 1) {
                    $nodes = substr($item->nodes,2);
                    $parentNodes = str_replace(',', '.', $nodes);
                }
                if ($item->navigate_rank == 1) {
                    $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['name'] = $item->privilege_name;
                    $rePrivilegeArr['navigates'][$item->navigate_rank][$one_i]['id'] = $item->id;
                    $one_i++;
                } elseif ($item->navigate_rank > 1) {
                    $newArr = [
                        'name' => $item->privilege_name,
                        'id'   => $item->id,
                    ];
                    $arrayKeys = "navigates.{$item->navigate_rank}.{$parentNodes}";
                    $this->array_num_set($rePrivilegeArr, $arrayKeys, $newArr);
                } elseif ($item->navigate_rank == 0) {
                    $newArr = [
                        'name' => $item->privilege_name,
                        'id'   => $item->id,
                    ];
                    $this->array_num_set($three_i, $parentNodes, $newArr);
                }
            }
            $rePrivilegeArr['execute_privilege'] = $three_i;
            unset($three_i);
        }

        return $rePrivilegeArr;
    }

    /**
     * 将以.分隔的字符串转成数组并符值到一个连续的数组上
     *
     * @param array  &$array 需处理的数组
     * @param string $key    需加到数组上的键名 以（.）分隔
     * @param array  $value  需添加到新数组上的值
     *
     * @return array
     */
    private function array_num_set(&$array, $key, $value = [])
    {
        if (is_null($key)) {
            return [];
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)][] = $value;

        //return $array;
    }
}
