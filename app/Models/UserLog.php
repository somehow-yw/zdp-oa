<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
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
    protected $table = 'user_logs';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',                      // 操作员ID
        'user_name',                    // 操作员名称
        'login_name',                   // 操作员登录名称
        'route_uses',                   // 操作路由
        'statistical_date',             // 操作日期
        'statistical_time',             // 操作次数
        'user_ip',                      // 操作IP
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
