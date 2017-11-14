<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpGoodsTypeSpecialAttribute extends Model
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
    protected $table = 'dp_goods_type_special_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',                      // 分类ID
        'attribute_name',               // 属性名称
        'format_type_id',               // 格式类型ID
        'must',                         // 是否必填属性
        // 属性可能的值 格式：[{"value":"值","unit":"单位","default":"是否选中Boolean","rule":"验证规则，如：string"}]
        'format_values',
        'format_rules',                 // 校验类型 格式：[{"integer"},{"string"}]
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

    /**
     * 所属分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goodsType()
    {
        return $this->belongsTo(DpGoodsType::class, 'type_id', 'id');
    }
}
