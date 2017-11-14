<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpGoodsPic extends Model
{
    // 最少图片数量
    const MIN_PIC_NUM = 3;

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
    protected $table = 'dp_goods_pic';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goodsid',                        // int 商品表的ID
        'ypic_path',                      // string 商品图片路径
        'sm1pic_path',                    // 没用
        'pic_path',                       // 没用
        'smpic_path',                     // 没用
        'ordernum',                       // int 排序
        'pic_remark',                     // string 图片说明
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
    protected $primaryKey = 'picid';

    /**
     * 所属商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goodsid', 'id');
    }

    /**
     * 获取图片信息
     *
     * @param       $id        int 记录ID
     * @param array $selectArr array 需获取的字段
     *
     * @return object Eloquent ORM Collect
     */
    public static function getPicInfoById($id, $selectArr)
    {
        return self::where('picid', $id)
            ->select($selectArr)
            ->first();
    }
}
