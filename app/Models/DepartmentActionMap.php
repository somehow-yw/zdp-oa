<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentActionMap extends Model
{
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
    protected $table = 'department_action_maps';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'department_id',                    // 部门ID
        'privilege_tag',                    // 权限标记
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
     * 所属部门(组)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
