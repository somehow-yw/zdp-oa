<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpRecommendGoods extends Model
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
    protected $table = 'dp_recommend_goods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_id',          //大区ID
        'goods_id',         //商品id
        'put_on_at',        //上架展示时间
        'pull_off_at',      //下架
        'pv',               //浏览量
        'sort_value',       //排序值
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

    public function goods()
    {
        return $this->hasOne(DpGoodsInfo::class, 'id', 'goods_id');
    }
}
