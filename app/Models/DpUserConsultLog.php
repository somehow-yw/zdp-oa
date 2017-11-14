<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DpUserConsultLog.
 * 会员咨询商品日志
 *
 * @package App\Models
 */
class DpUserConsultLog extends Model
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
    protected $table = 'dp_bodalog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bduid',                        // 会员ID
        'bdgid',                        // 商品ID
        'goodtag',                      // 商品类型 如：主打商品
        'goodrecommend',                // 是否推荐商品
        'telnumber',                    // 电话号码
        'bdnum',                        // 拨打次数
        'bddate',                       // 咨询日期
        'endbdtime',                    // 最后咨询时间
        'bdrip',                        // 最后咨询IP
        'otherinfo',                    // 其它信息 备注
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

    public static function addLog(array $addArr)
    {
        return self::create($addArr);
    }

    public static function getConsultLog(array $whereArr, array $selectArr)
    {
        return self::where($whereArr)
            ->select($selectArr);
    }
}
