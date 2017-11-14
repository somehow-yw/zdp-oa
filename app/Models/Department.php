<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    // 状态 status
    const NORMAL_STATUS = 1;        // 正常
    const CLOSE_STATUS = 2;         // 关闭
    const DELETE_STATUS = 3;        // 删除

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
    protected $table = 'departments';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'department_name',              // 部门名称
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

    /**
     * 操作员
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->hasMany(User::class, 'department_id', 'id');
    }

    /**
     * 权限映射
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function departmentActionMap()
    {
        return $this->hasMany(DepartmentActionMap::class, 'department_id', 'id');
    }
}
