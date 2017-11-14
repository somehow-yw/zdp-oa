<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpMessageImg extends Model
{
    protected $connection = 'mysql_zdp_main';


    protected $table = 'dp_message_img';

    //主键
    protected $primaryKey = 'id';
    //表名是否打上时间戳
    public $timestamps = true;

    //白名单
    protected $fillable = [
        'message_id',   //对应反馈id
        'img_url',      //对应图片链接地址
        'type' ,
    ];

    public function messages(){
        return $this->belongsTo(DpMessage::class,'message_id','id');
    }
}
