<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpVersionManageLog extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_zdp_main';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'dp_version_manage_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'big_version',                  // 大版本号
        'small_version',                // 小版本号
        'develop_version',              // 开发版本号
        'remark',                       // 备注信息
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 返回最后一条记录(最新版本)的信息
     * @return object Eloquent ORM Collect
     */
    public static function getNewVersionInfo()
    {
        return self::orderBy('id', 'desc')
            ->first();
    }
}
