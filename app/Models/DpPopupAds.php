<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpPopupAds extends Model
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
    protected $table = 'dp_popup_ads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_id',          //大区ID
        'ads_title',        //广告标题
        'show_time',        //广告展示时间 单位：秒
        'put_on_at',        //上架展示时间
        'pull_off_at',      //下架
        'pv',               //浏览量
        'link_url',         //链接地址
        'image',            //图片地址
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
}
