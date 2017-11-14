<?php
/**
 * Created by PhpStorm.
 * User: Chen
 * Date: 2017/9/1
 * Time: 9:16
 */
namespace App\Services;

use App\Models\DpMessage;
use App\Models\DpShopInfo;
use App\Repositories\Tickl\TicklRepositories;
use Zdp\ServiceProvider\Data\Models\SpMessages;

class TicklService
{
    private $ticklRepositories;

    public function __construct(
        TicklRepositories $ticklRepositories
    )
    {
        $this->ticklRepositories = $ticklRepositories;
    }

    /**
     * 获取找冻品网用户反馈
     *
     * @param int $type 0=>反馈未处理；2=>反馈已处理
     * @param int $pageSize
     * @param int $pageNum
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function GetTicking($type, $pageSize, $pageNum)
    {
        $reData = $this->ticklRepositories->GetTicking($type, $pageSize, $pageNum);
        foreach ($reData['data'] as &$item) {
            if(!empty($item['trenchnum'])){
                $item['trenchnum'] = DpShopInfo::$shopTypeName[$item['trenchnum']];
            }
        }
        return $reData;
    }

    /**
     * 获取找冻品网某一反馈详情
     *
     * @param int $type 2=>已处理反馈详情 不传默认显示未反馈详情
     * @param int $tickingId 反馈id
     * @return mixed
     */
    public function getTickingInfo($type, $tickingId)
    {
        $query = DpMessage::query()
            ->where('id', $tickingId)
            ->with(
                [
                    'img' => function ($query) {
                        $query->select(
                            'message_id',
                            'img_url'
                        );
                    }
                ]
            )
            ->leftjoin('dp_shanghuinfo as i', 'dp_messages.shid', '=', 'i.shId')
            ->leftjoin('dp_shopinfo as s', 'i.shopId', '=', 's.shopId')
            ->select(
                'dp_messages.id as id',
                'i.xingming as userName',
                's.dianPuName as shopName',
                'i.lianxiTel',

                's.province',
                's.city',
                's.county',
                's.xiangXiDiZi',

                'dp_messages.message as question',
                's.trenchnum',
                'dp_messages.mesgtime as addTime'
            );
        if ($type == DpMessage::MSGACT_OK) {
            $query->addSelect(
                'dp_messages.yijian as answer',
                'dp_messages.cltime as updateTime'
            );
        }
        $re = $query->first()
            ->toArray();
        $re['trenchnum'] = DpShopInfo::$shopTypeName[$re['trenchnum']];
        $re['address'] = $re['province'] . $re['city'] . $re['county'] . $re['xiangXiDiZi'];
        return $re;
    }

    /**
     * 获取服务商用户反馈
     *
     * @param int $type 0=>反馈未处理；2=>反馈已处理
     * @param int $pageSize
     * @param int $pageNum
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function GetSpTicking($type, $pageSize, $pageNum)
    {
        $reData = $this->ticklRepositories->GetSpTicking($type, $pageSize, $pageNum);
        return $reData;
    }

    /**
     * 获取服务商某一反馈详情
     *
     * @param int $type 2=>已处理反馈详情 不传默认显示未反馈详情
     * @param int $tickingId 反馈id
     * @return mixed
     */
    public function getSpTickingInfo($type, $tickingId)
    {
        $query = SpMessages::query()
            ->where('sp_messages.id', '=', $tickingId)
            ->with(
                [
                    'img' => function ($query) {
                        $query->select(
                            'message_id',
                            'img_url'
                        );
                    }
                ]
            )
            ->leftjoin('users as u','sp_messages.shid','=','u.id')
            ->leftjoin('service_providers as s','u.sp_id','=','s.zdp_user_id')
            ->select(
                'sp_messages.id as id',
                'u.user_name as userName',
                'u.mobile_phone as phone',
                'u.shop_name as shopName',
                'u.province_id as province_id',
                'u.county_id as county_id',
                'u.city_id as city_id',
                'sp_messages.message as question',
                's.shop_name as spName',
                'sp_messages.mesgtime as addTime'
            );
        if ($type == DpMessage::MSGACT_OK) {
            $query->addSelect(
                'sp_messages.yijian as answer',
                'sp_messages.cltime as updateTime'
            );
        }
        return $query->first();
    }
}