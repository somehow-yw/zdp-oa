<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

/**
 * Class User.
 * 会员信息
 * @package App\Models
 *
 * @property integer    $id
 * @property string     $user_name
 * @property Collection $addresses
 * @property string     $wechat_openid
 * @property string     $shop_name
 * @property ShopType   $shopType
 * @property integer    $shop_type
 * @property string     $default_address
 * @property string     $mobile_phone
 * @property integer    $status
 * @property string     $province
 * @property string     $city
 * @property string     $county
 * @property string     $full_address
 *
 */
class DpSpUser extends Model
{
    use SoftDeletes;

    // 会员状态 status
    const NOT_REGISTER = -1;         // 尚未注册
    const NOT_PERFECT  = 0;          // 信息待完善
    const ENDING       = 1;          // 待核中
    const PASS         = 2;          // 通过
    const DENY         = 3;          // 拒绝


    /**
     * 状态数组
     *
     * @var array
     */
    protected static $statusArr = [
        self::NOT_PERFECT => '信息待完善',
        self::ENDING      => "待核中",
        self::PASS        => "通过",
        self::DENY        => "拒绝",
    ];

    protected $connection = 'mysql_service_provider';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sp_id',                        // 服务商ID
        'wechat_openid',                // 会员关注当前公众号的微信OPENID
        'wechat_nickname',              // 微信昵称
        'wechat_avatar',                // 微信头像
        'mobile_phone',                 // 注册手机号
        'user_name',                    // 会员真实姓名
        'shop_name',                    // 店铺名称
        'shop_type_id',                 // 店铺类型对应ID
        'province_id',                  // 所在省对应ID
        'city_id',                      // 所在市对应ID
        'county_id',                    // 所在县对应ID
        'address',                      // 所在地址
        'status',                       // 会员状态
        'shipping_address_id',          // 会员默认收货地址ID
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
