<?php
/**
 * Created by PhpStorm.
 * User: Chen
 * Date: 2017/9/1
 * Time: 9:40
 */

namespace App\Repositories\Tickl;

use App\Models\DpMessage;
use App\Repositories\Tickl\Contracts\TicklRepositories as RepositoriesContract;
use Zdp\ServiceProvider\Data\Models\SpMessages;

class TicklRepositories implements RepositoriesContract
{


    /**
     * 获得所有找冻品网用户消息反馈
     *
     * @param int $type 0=>未完成；2=>已完成
     * @param int $pageSize
     * @param int $pageNum
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function GetTicking($type, $pageSize, $pageNum)
    {
        $query = DpMessage::query();
        $query->where('messages_type', '=', DpMessage::ZDP_MESSAGE)
            ->leftjoin('dp_shanghuinfo as i', 'dp_messages.shid', '=', 'i.shId')
            ->leftjoin('dp_shopinfo as s', 'i.shopId', '=', 's.shopId');
        if ($type == DpMessage::MSEACT_NO) {
            $query->where('msgact', DpMessage::MSEACT_NO)
            ->orderBy('dp_messages.id','desc');
            return $query->paginate(
                $pageSize,
                [
                    'dp_messages.id as id',
                    'i.xingming as userName',
                    's.dianPuName as shopName',
                    'dp_messages.message as question',
                    'i.lianxiTel',
                    's.trenchnum',
                    'dp_messages.mesgtime as addTime'
                ],
                null,
                $pageNum
            )->toArray();
        } elseif ($type == DpMessage::MSGACT_OK) {
            $query->where('msgact', DpMessage::MSGACT_OK);
            return $query->paginate(
                $pageSize,
                [
                    'dp_messages.id as id',
                    's.dianPuName as shopName',
                    'dp_messages.message as question',
                    'dp_messages.yijian as answer',
                    'dp_messages.ope_name as replayName',
                    's.trenchnum',
                    'dp_messages.mesgtime as addTime',
                    'dp_messages.cltime as updateTime'
                ],
                null,
                $pageNum
            )->toArray();
        }
    }

    /**
     * 获得所有服务商用户消息反馈
     *
     * @param int $type 0=>未完成；2=>已完成
     * @param int $pageSize
     * @param int $pageNum
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function GetSpTicking($type, $pageSize, $pageNum)
    {
        $query = SpMessages::query();
        $query->leftjoin('users as u','sp_messages.shid','=','u.id')
            ->leftjoin('service_providers as s','u.sp_id','=','s.zdp_user_id');
        if ($type == DpMessage::MSEACT_NO) {
            $query->where('msgact', DpMessage::MSEACT_NO);
            return $query->paginate(
                $pageSize,
                [
                    'sp_messages.id as id',
                    'u.user_name as userName',
                    'u.shop_name as shopName',
                    'sp_messages.message as question',
                    's.shop_name as spName',
                    'sp_messages.mesgtime as addTime'
                ],
                null,
                $pageNum
            )->toArray();
        } elseif ($type == DpMessage::MSGACT_OK) {
            $query->where('msgact', DpMessage::MSGACT_OK);
            return $query->paginate(
                $pageSize,
                [
                    'sp_messages.id as id',
                    'u.user_name as userName',
                    'u.shop_name as shopName',
                    'sp_messages.message as question',
                    'sp_messages.yijian as answer',
                    'sp_messages.ope_name as replayName',
                    's.shop_name as spName',
                    'sp_messages.mesgtime as addTime',
                    'sp_messages.cltime as updateTime'
                ],
                null,
                $pageNum
            )->toArray();
        }
    }

}