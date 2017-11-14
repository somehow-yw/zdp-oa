<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DpBannerInfo.
 * Banner信息
 *
 * @package App\Models
 */
class DpBannerInfo extends Model
{
    use SoftDeletes;

    // 前端请求数据时的显示状态
    const AWAIT_PUT_ON = 1;     // 待上架
    const PUT_ON       = 2;     // 上架
    const PULL_OFF     = 3;     // 下架

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
    protected $table = 'dp_banners_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',              // 分类ID 在banner配置文件中
        'area_id',              // 所属大区ID
        'title',                // 标题
        'cover_pic',            // 封面图
        'goods_id',             // 商品ID
        'shop_id',              // 店铺ID
        'location',             // 放置位置 如：buyer_index=买家首页 seller_index=卖家管理首页 在banner配置文件中
        'put_on_at',            // 上架(展示)时间
        'pull_off_at',          // 下架时间
        'pv',                   // 点击量
        'position',             // 展示位置
        'redirect_link',        // 跳转链接
        'content',              // 内容信息
        'add_the',              // 添加者姓名
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
}
