<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpShopAppraiseInfo extends Model
{
    //数据库
    protected $connection = 'mysql_zdp_main';
    //表名
    protected $table = 'dp_shop_appraise_info';
    //是否打上时间戳
    public $timestamps = false;
    //主键
    protected $primaryKey = 'id';
    //白名单
    protected $fillable = [
        'shopid',   //对应店铺id
        'appraise_num', //店铺评论总数
        'good_appraise_num', //店铺好评数
        'good_appraise_ratio',//店铺好评率
    ];

}
