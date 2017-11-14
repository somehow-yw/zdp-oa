<?php
/**
 * Created by PhpStorm.
 * User: j5110
 * Date: 2016/8/26
 * Time: 17:28
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpRiseRecord extends Model
{
    use SoftDeletes;

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
    protected $table = 'dp_rise_record';

    /**
     * 批量插入白名单
     *
     * @var array
     */
    protected $fillable = [
        'divide_id',                                    //大区ID
        'created_date',                                 //创建日期
        'goods_id',                                     //商品ID
        'goods_name',                                   //商品名字
        'yesterday_sell_num',                           //昨日销售数量
        'yesterday_price',                              //昨日价格
        'supplier_name',                                //供应商名字
        'now_price',                                    //当前价格
        'range',                                        //涨价或降价的幅度
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
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}