<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionPrivilege extends Model
{
    // 状态 status
    const NORMAL_STATUS = 1;        // 正常
    const CLOSE_STATUS = 2;         // 关闭

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'action_privileges';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',                    // 父权限ID
        'nodes',                        // 权限节点ID串
        'privilege_name',               // 权限名称
        'privilege_tag',                // 权限代号（标记）
        'navigate_rank',                // 导航级别
        'route',                        // URL路由
        'status',                       // 状态
        'remark',                       // 备注
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
