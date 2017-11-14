<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpPianquDivide extends Model
{
    // 片区ID对应电话
    const PARENT_COMPANY = 0;       // 总公司
    const SICHUAN_AREA   = 2;       // 四川片区
    const CHONGQING_AREA = 3;       // 重庆片区

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
    protected $table = 'dp_pianqu_divide';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dividename',
        'provinceidtxt',
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 对应市场
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function market()
    {
        return $this->hasMany(DpMarketInfo::class, 'divideid', 'id');
    }
}
