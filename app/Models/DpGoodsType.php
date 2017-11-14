<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DpGoodsType extends Model
{
    use SoftDeletes;

    // fid为1的纪录表示主类
    const MAIN_SORT_FID = 1;

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
    protected $table = 'dp_goods_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sort_name',                    // string 分类名
        'area_id',                      // 所属大区ID
        'fid',                          // int 父类ID
        'nodeid',                       // string 所有的祖先ID串(包括自身) ','号分隔
        'beizhu',                       // 备注
        'keywords',                     // 分类关键词
        'goods_number',                 // 有效商品数量
        'series',                       // 分类级数 如：1=一级 2=二级 ...
        'sort_value',                   // 排列顺序 按分类级数分别排序
        'pic_url',                      // 分类图片
        'sale_goods_num',               // 该分类在售商品数量(对用户可见)
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
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 对应分类特殊属性
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function typeSpecialAttr()
    {
        return $this->hasMany(DpGoodsTypeSpecialAttribute::class, 'type_id', 'id');
    }
}
