<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    // 状态 user_status
    const NORMAL_STATUS = 1;        // 正常
    const CLOSE_STATUS = 2;         // 关闭
    const DELETE_STATUS = 3;        // 删除

    // 是否绑定微信账号 we_chat_binding
    const BINDING_WE_CHAT_NOT = 1;      // 未绑定
    const BINDING_WE_CHAT_YES = 2;      // 已绑定

    // 不可删除及更改信息的操作员姓名
    const USER_NAME_ADMIN = 'admin';        // admin管理员
    const USER_NAME_ROOT = 'root';          // root超级管理员

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name',                    // 会员姓名
        'department_id',                // 所属部门ID
        'login_name',                   // 登录名，这里为手机号
        'we_chat_binding',              // 是否绑定微信
        'user_status',                  // 会员账户状态
        'salt',                         // 密码干扰成分（盐）
        'password',                     // 登录密码
        'remark',                       // 备注
        'remember_token',               // 登录记住功能
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'salt',
        'remember_token',
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 不可删除的操作员
     *
     * @return array
     */
    public static function notDeleteUsers()
    {
        return [
            self::USER_NAME_ADMIN,
            self::USER_NAME_ROOT,
        ];
    }

    /**
     * 不可更改的操作员
     *
     * @return array
     */
    public static function notUpdateUsers()
    {
        return [
            self::USER_NAME_ROOT,
        ];
    }

    /**
     * 具有超级权限的操作员
     *
     * @return array
     */
    public static function superActionUsers()
    {
        return [
            self::USER_NAME_ROOT,
            self::USER_NAME_ADMIN,
        ];
    }

    /**
     * 权限映射
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userActionMap()
    {
        return $this->hasMany(UserActionMap::class, 'user_id', 'id');
    }

    /**
     * 所属部门(组)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
