<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DpGoodsInspectionReport.
 * 商品检验报告图片
 * @package App\Models
 */
class DpGoodsInspectionReport extends Model
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
    protected $table = 'dp_goods_inspection_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goods_id',                         // int 商品表的ID
        'picture_add',                      // string 商品图片路径
        'sort_value',                       // int 排序值
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

    /**
     * 所属商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(DpGoodsInfo::class, 'goods_id', 'id');
    }

    /**
     * 获取检验报告图片信息
     *
     * @param       $id        int 记录ID
     * @param array $selectArr array 需获取的字段
     *
     * @return object Eloquent ORM Collect
     */
    public static function getPicInfoById($id, $selectArr = ['*'])
    {
        return self::where('id', $id)
            ->select($selectArr)
            ->first();
    }
}
