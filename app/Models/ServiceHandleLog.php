<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceHandleLog extends Model
{
    const ADMIN_CLOSE = 0; // 关闭管理员
    const ADMIN_ADD = 1; // 新增管理员
    const ADMIN_OPEN = 2; // 开通管理员

    const API_UPDATE = 10; // 更新接口

    const SERVICE_CLOSE = 20;  // 关闭
    const SERVICE_EDIT = 21;  // 编辑
    const SERVICE_APPLY = 22;  // 申请开通
    const SERVICE_CONFIRM = 23;  // 确认通过
    const SERVICE_DEL = 24;  // 删除

    public static $optionArr = [
        self::ADMIN_CLOSE,
        self::ADMIN_ADD,
        self::ADMIN_OPEN,
        self::API_UPDATE,
        self::SERVICE_CLOSE,
        self::SERVICE_EDIT,
        self::SERVICE_APPLY,
        self::SERVICE_CONFIRM,
        self::SERVICE_DEL,
    ];

    protected $table = 'service_handle_log';

    protected $fillable = [
        'sp_id',
        'uid',
        'operate',
    ];

    protected $primaryKey = 'id';

    public $timestamps = false;
}
