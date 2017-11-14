<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpGoodsSpecialAttribute extends Model
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
    protected $table = 'dp_goods_special_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * attr_value格式说明：
     * [
     *  {"constraint_id":1,
     *   "name":"约束(属性)名称",
     *   "format_type_id":3,
     *   "values":[
     *    {"value":"值","unit":"单位(个)"},{...}
     *    ]
     *   },{...}
     * ]
     */
    protected $fillable = [
        'goodsid',                              // int 对应商品表ID
        'propeid',                              // 商品分类特殊属性ID
        'prope_name',                           // 属性名称
        'prope_value',                          // 属性值（多值用‘,’号分隔）
        'attr_value',                           // 属性内容 JSON
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
