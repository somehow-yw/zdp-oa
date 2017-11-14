<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpServiceMessageSendUser extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_zdp_main';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'dp_service_message_send_users';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'we_chat_openid',                   // 微信OPENID
        'operate_type',                     // 最后操作类型
        'shop_area_id',                     // 所在大区
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
    public $timestamps = false;

    /**
     * 对应会员信息,由于会出现删除，所在这里为一对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->hasMany(DpShangHuInfo::class, 'OpenID', 'we_chat_openid');
    }
}
