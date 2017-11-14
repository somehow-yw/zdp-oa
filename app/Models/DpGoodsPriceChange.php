<?php
/**
 * Created by PhpStorm.
 * User: j5110
 * Date: 2016/8/29
 * Time: 11:55
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class DpGoodsPriceChange extends Model
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
    protected $table = 'dp_goodprice_change';

    /**
     * 批量插入白名单
     *
     * @var array
     */
    protected $fillable = [
        'goodid',                                    //商品ID
        'basicid',                                   //商品属性ID
        'shid',                                      //价格修改者ID
        'shtel',                                     //价格修改者电话
        'old_price',                                 //变动前价格
        'new_price',                                 //当前价格
        'edit_time',                                 //涨价或降价的幅度
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
}