<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpAppraiseDisposeLog extends Model
{
    // 管理后端查询状态
    const PENDING_DISPOSE = 1;  // 待处理
    const ACCOMPLISH = 2;       // 已完成
    const DELETE = 3;           // 已删除
    const HIGH_PRAISE = 4;      // 中好评

    //日志的操作类型说明字段 对应字段 status的值
    const STATUS_ALTER = 0;      //修改
    const STATUS_DELETE = 1;     //删除
    const STATUS_RESET = 2;      //重置

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
    protected $table = 'dp_appraise_dispose_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'appraise_id',                  // 评价ID
        'sell_shop_id',                 // 卖家店铺ID
        'goods_id',                     // 商品ID
        'order_goods_id',               // 订单商品ID
        'sub_order_no',                 // 子订单编号
        'admin_id',                     // 处理者ID
        'admin_name',                   // 处理者名称
        'content',                      // 处理的相关内容
        'status',                       // 处理状态(0:为修改；1：为删除；2：为重置)
        'remark',                       // 处理备注
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
