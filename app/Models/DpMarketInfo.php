<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpMarketInfo extends Model
{
    const TYPE_YIPI = 1;    // 一批商
    const TYPE_ERPI = 0;    // 二批商

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
    protected $table = 'dp_pianqu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pianquId',         // int 市场id
        'province',         // int 省份id
        'city',             // int 城市id
        'county',           // int 区县id
        'pianqu',           // string 市场名称
        'shangHuNum',       // int 商户数量，不准
        'beizhu',           // string
        'yipishang',        // int see TYPE_XXX
        'adminId',          // int 添加操作管理员id
        'adminName',        // string 添加操作管理员名称
        'addTime',          // timestamp 添加时间
        'divideid',         // int 片区id DpPianquDivide::$id
        'goods_number',     // 有效商品数量
        'sale_goods_num',   // 该市场在售商品数量(对用户可见)
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
    protected $primaryKey = 'pianquId';

    /**
     * 对应店铺表
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function market()
    {
        return $this->hasMany(DpShopInfo::class, 'pianquId', 'pianquId');
    }

    /**
     * 所属片区
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(DpPianquDivide::class, 'divideid', 'id');
    }
}
