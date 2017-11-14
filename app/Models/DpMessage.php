<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpMessage extends Model
{

    const MSGACT_OK = 2;   //已回复
    const MSEACT_NO = 0;   //未回复
    //

    const ZDP_MESSAGE = 1;  //找冻品网
    const SP_MESSAGE  = 2;  //服务商
    protected $connection = 'mysql_zdp_main';


    protected $table = 'dp_messages';

    //主键
    protected $primaryKey = 'id';
    //表名是否打上时间戳
    public $timestamps = false;

    //白名单
    protected $fillable = [
        'shid',     //用户id
        'message',  //用户反馈内容
        'mesgtime', //用户反馈时间
        'formip',   //用户反馈ip
        'msgact',   //处理状态 0=>未处理 1=>正在处理 2=>处理完成
        'yijian',   //处理内容
        'cltime',    //处理时间
        'ope_name',  //回复者姓名
    ];

    public function img(){
        return $this->hasMany(DpMessageImg::class,'message_id','id');
    }
}
