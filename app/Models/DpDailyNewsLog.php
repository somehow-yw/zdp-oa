<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpDailyNewsLog extends Model
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
    protected $table = 'dp_daily_news_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_id',                          // 大区ID
        'send_date',                        // 推送日期
        'delivery_number',                  // 送达人数
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
