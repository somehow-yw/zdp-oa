<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpNewsManageConfiguration extends Model
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
    protected $table = 'dp_news_manage_configurations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_id',                          // 大区ID
        'edit_user_id',                     // 编辑员ID
        'review_user_id',                   // 审查员ID
        'edit_remind_time',                 // 未编辑提醒时间
        'review_remind_time',               // 未审查提醒时间
        'send_time',                        // 推文发送时间
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
}
