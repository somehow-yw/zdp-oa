<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品分类约束表
 *
 * Class DpGoodsConstraints
 *
 * @package App\Models
 * @property $id                  integer 约束id
 * @property $type_id             integer 分类id
 * @property $constraint_type     integer 约束类型 0-类型约束 1-规格约束
 * @property $format_type_id      integer 约束类型id
 * @property $format_rule         string 约束规则
 * @property $format_values       string 约束值
 *
 */
class DpGoodsConstraints extends Model
{
    // 类型约束 0
    const TYPE_CONSTRAINT = 0;
    // 规格约束 1
    const SPEC_CONSTRAINT = 1;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'dp_goods_constraints';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_zdp_main';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',                         // string 分类id
        'constraint_type',                 // int 约束类型 0-类型约束 1-规格约束
        'format_type_id',                  // int 格式类型id
        'format_rule',                     // json 格式规则约束json配置文件 格式：[{"integer"},{"string"}]
        // json 格式约束值json 格式：[{"value":"值","unit":"单位","default":"是否选中Boolean","rule":"验证规则，如：string"}]
        'format_values',
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
