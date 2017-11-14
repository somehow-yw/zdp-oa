<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as MongoModel;

class DpGoodsOperationLog extends MongoModel
{
    // 处理类型 type
    const ADD_GOODS             = 1;  // 添加
    const AUDIT_GOODS           = 2;  // 审核通过
    const UPDATE_GOODS          = 3;  // 修改
    const REFUSED_GOODS         = 4;  // 审核拒绝通过
    const STOP_SALE_GOODS       = 5;  // 商品下架
    const SALE_GOODS            = 6;  // 商品上架
    const DELETE_GOODS          = 7;  // 商品删除
    const UPDATE_PRICE_GOODS    = 8;  // 商品改价
    const REFRESH_PRICE_GOODS   = 9;  // 商品价格刷新
    const RECOVERY_TO_AUDIT     = 10; // 恢复待审核
    const DELETE_TO_READY_AUDIT = 11; // 从删除状态到待审核

    public static $operationType = [
        self::ADD_GOODS             => '添加商品',
        self::AUDIT_GOODS           => '审核通过',
        self::UPDATE_GOODS          => '修改商品信息',
        self::REFUSED_GOODS         => '审核拒绝',
        self::STOP_SALE_GOODS       => '商品下架',
        self::SALE_GOODS            => '商品上架',
        self::DELETE_GOODS          => '商品删除',
        self::UPDATE_PRICE_GOODS    => '商品改价',
        self::REFRESH_PRICE_GOODS   => '商品价格刷新',
        self::RECOVERY_TO_AUDIT     => '恢复待审核',
        self::DELETE_TO_READY_AUDIT => '已删除到待审核',
    ];
    public static $identityMap   = [
        self::ADMIN    => "后台管理员",
        self::SUPPLIER => "供应商",
    ];
    // 操作者身份 identity
    const SUPPLIER = 1;     // 供应商
    const ADMIN    = 2;     // 管理员

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $collection = 'dp_goods_operation_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goods_id',         // 商品ID
        'type',             // 处理类型
        'identity',         // 操作者身份
        'user_id',          // 操作者ID
        'user_name',        // 操作者姓名
        'note',             // 备注信息 如：拒绝原因等
        'created_at',       // 日志创建时间
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
    protected $primaryKey = '_id';
}
