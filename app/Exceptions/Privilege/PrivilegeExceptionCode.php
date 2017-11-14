<?php

namespace App\Exceptions\Privilege;

/**
 * Privilege exception code definitions
 */
final class PrivilegeExceptionCode
{
    /**
     * 没有权限
     */
    const NOT_PRIVILEGE = 101;

    /**
     * 父级权限不存在
     */
    const PARENT_PRIVILEGE_NULL = 102;

    /**
     * 权限写入失败
     */
    const WRITE_PRIVILEGE_FAILURE = 103;

    /**
     * 权限标记已经存在
     */
    const PRIVILEGE_TAG_EXISTING = 104;

    /**
     * 与父级权限级别不相符合
     */
    const PRIVILEGE_RANK_ERROR = 105;

    /**
     * 权限不存在
     */
    const PRIVILEGE_NULL = 106;

    /**
     * 权限已有状态不可更改为当前状态
     */
    const PRIVILEGE_STATUS_CANNOT_CHANGED = 107;

    /**
     * 还未分配任何权限
     */
    const WITHOUT_ANY_PRIVILEGE = 108;
}
