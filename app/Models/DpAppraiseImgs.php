<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpAppraiseImgs extends Model
{
    const GOODS_APPRAISES = 1; //物品评价图片
    const SHOP_APPRAISES = 2;  //商铺评价图片

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_zdp_main';

    /**
     * 表名称
     * @var string
     */
    protected $table = 'dp_appraises_imgs';

    /**
     * 白名单
     * @var array
     */
    protected $fillable = [
      'appraise_id',   //评论的id
      'img_url',       //评论图片url
      'type',          //评论类型 现在还未适用
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 设置主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 对评论表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function appraise()
    {
        return $this->belongsTo(DpServiceAppraises::class, 'appraise_id', 'id');
    }
}
