<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/27
 * Time: 12:06
 */

namespace App\Services\MarketingCenter;

use Zdp\Main\Data\Models\ExchangeTicketType;
use App\Exceptions\MarketingCenter\ExchangeTicketException;
use Zdp\Main\Data\Models\DpExchangeTicket;
use Zdp\Main\Data\Models\DpExchangeTicketBuyLog;
use Carbon\Carbon;

/**
 * 兑换券处理
 * Class ExchangeTicketService
 * @package app\Services\MarketingCenter
 */
class ExchangeTicketService
{
    /**
     * 兑换券分类获取
     * @return array
     */
    public function getType()
    {
        return ExchangeTicketType::getType();
    }

    /**
     * 兑换券添加
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/exchange-ticket
     *
     * @param array $requestArr array 请求参数
     *
     * @return DpExchangeTicket|null
     * @throws \App\Exceptions\MarketingCenter\ExchangeTicketException
     */
    public function add(array $requestArr)
    {
        $typeArr = ExchangeTicketType::getType();
        if (!array_key_exists($requestArr['type_no'], $typeArr)) {
            throw new ExchangeTicketException(ExchangeTicketException::TYPE_ERROR);
        }

        $addArr = [
            'type_no'   => $requestArr['type_no'],
            'image'     => $requestArr['image'],
            'title'     => $requestArr['title'],
            'price'     => $requestArr['price'],
            'worth'     => $requestArr['worth'],
            'unit'      => $typeArr[$requestArr['type_no']]['unit'],
            'sell_time' => $requestArr['sell_time'],
            'end_time'  => $requestArr['end_time'],
            'remark'    => empty($requestArr['remark']) ? '' : $requestArr['remark'],
        ];

        return DpExchangeTicket::create($addArr);
    }

    /**
     * 兑换券上、下架
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/exchange-ticket
     *
     * @param array $requestArr array 请求参数
     *
     * @return int
     */
    public function onSell(array $requestArr)
    {
        // 注：option_type 操作选项 3=停用
        if (!empty($requestArr['option_type'])) {
            $carbon = Carbon::now();
            switch ($requestArr['option_type']) {
                case DpExchangeTicket::STOP_SELL:
                    // 停用
                    $requestArr['end_time'] = $carbon->format('Y-m-d H:i:s');
                    $requestArr['sell_time'] = $carbon->subSecond()->format('Y-m-d H:i:s');
                    break;
            }
        }
        $updateArr = [
            'sell_time' => $requestArr['sell_time'],
            'end_time'  => $requestArr['end_time'],
        ];

        return DpExchangeTicket::query()->where('id', $requestArr['id'])->update($updateArr);
    }

    /**
     * 兑换券查询
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/exchange-ticket
     *
     * @param array $requestArr array 请求参数
     *
     * @return array
     */
    public function getList(array $requestArr)
    {
        $status = $requestArr['status'];
        $typeNo = empty($requestArr['type_no']) ? '' : $requestArr['type_no'];
        $select = [
            'id',
            'type_no',
            'title',
            'image',
            'price',
            'worth',
            'unit',
            'sell_time',
            'end_time',
        ];

        $query = DpExchangeTicket::query();
        $query = $this->getListQueryWhere($query, $status, $typeNo);
        $query = $query->select($select);
        $exchangeTicketInfo = $query->paginate($requestArr['size']);

        $returnArr = [
            'page'  => (int)$requestArr['page'],
            'total' => $exchangeTicketInfo->total(),
            'lists' => [],
        ];
        if ($exchangeTicketInfo->count()) {
            $returnArr['lists'] = $exchangeTicketInfo->items();
        }

        return $returnArr;
    }

    /**
     * 兑换券购买记录查询
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/exchange-ticket
     *
     * @param array $requestArr array 请求参数
     *
     * @return array
     */
    public function buyList(array $requestArr)
    {
        $status = empty($requestArr['status']) ? 0 : $requestArr['status'];
        $typeNo = empty($requestArr['type_no']) ? '' : $requestArr['type_no'];
        $buyersShopId = empty($requestArr['shop_id']) ? 0 : $requestArr['shop_id'];
        $select = [
            'id',
            'type_no',
            'title',
            'spend AS price',
            'worth',
            'unit',
            'created_at AS buy_time',
            'status',
            'shop_id',
            'user_id',
        ];

        $query = DpExchangeTicketBuyLog::query();
        $query = $this->getBuyListQueryWhere($query, $status, $typeNo, $buyersShopId);
        $query = $query->select($select);
        $exchangeTicketInfo = $query->paginate($requestArr['size']);

        $returnArr = [
            'page'  => (int)$requestArr['page'],
            'total' => $exchangeTicketInfo->total(),
            'lists' => [],
        ];
        if ($exchangeTicketInfo->count()) {
            foreach ($exchangeTicketInfo as $key => $item) {
                $shopName = $item->shop->dianPuName;
                $userTel = $item->customer->lianxiTel;
                //echo $shopName;exit;
                $item->shop_name = $shopName;
                $item->user_tel = $userTel;
                $item->type_name = ExchangeTicketType::getType($item->type_no)['name'];
                unset($item->shop);
                unset($item->customer);
                unset($item->shop_id);
                unset($item->user_id);
                unset($item->type_no);
            }
            $returnArr['lists'] = $exchangeTicketInfo->items();
        }

        return $returnArr;
    }

    /**
     * 兑换券购买记录状态更改
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/exchange-ticket
     *
     * @param array $requestArr array 请求参数
     *
     * @return array
     * @throws ExchangeTicketException
     */
    public function updateExchangeStatus(array $requestArr)
    {
        $updateArr = [
            'status'       => $requestArr['status'],
            'exchanged_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        $query = DpExchangeTicketBuyLog::query()->where('id', $requestArr['id']);
        switch ($requestArr['status']) {
            case DpExchangeTicketBuyLog::ALREADY_EXCHANGE:
                // 改为已兑换
                $query = $query->where('status', DpExchangeTicketBuyLog::NOT_EXCHANGE);
                break;
            default:
                throw new ExchangeTicketException(ExchangeTicketException::EXCHANGE_STATUS_CANNOT);
        }

        $updateNum = $query->update($updateArr);
        if (empty($updateNum)) {
            throw new ExchangeTicketException(ExchangeTicketException::EXCHANGE_STATUS_CANNOT);
        }

        return ['id' => $requestArr['id'], 'status' => $requestArr['status']];
    }

    /**
     * 组装列表查询条件
     *
     * @param $query  \Illuminate\Database\Eloquent\Builder 查询对象
     * @param $status integer 查询状态
     * @param $typeNo string 分类编号
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getListQueryWhere($query, $status, $typeNo = '')
    {
        $carbonData = Carbon::now();
        $dataTxt = $carbonData->format('Y-m-d H:i:s');
        switch ($status) {
            case DpExchangeTicket::OFF_SELL:
                $query = $query->where('sell_time', '>', $dataTxt)
                    ->where('end_time', '>', $dataTxt);
                break;
            case DpExchangeTicket::ON_SELL:
                $query = $query->where('sell_time', '<=', $dataTxt)
                    ->where('end_time', '>', $dataTxt);
                break;
            case DpExchangeTicket::STOP_SELL:
                $query = $query->where('sell_time', '<=', $dataTxt)
                    ->where('end_time', '<=', $dataTxt);
                break;
        }
        if (!empty($typeNo)) {
            $query = $query->where('type_no', $typeNo);
        }

        return $query;
    }

    /**
     * 组装购买列表查询条件
     *
     * @param $query        \Illuminate\Database\Eloquent\Builder 查询对象
     * @param $status       integer 查询状态
     * @param $typeNo       string 分类编号
     * @param $buyersShopId integer 购买者店铺ID
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBuyListQueryWhere($query, $status, $typeNo, $buyersShopId)
    {
        if (!empty($status)) {
            $query = $query->where('status', $status);
        }
        if (!empty($typeNo)) {
            $query = $query->where('type_no', $typeNo);
        }
        if (!empty($buyersShopId)) {
            $query = $query->where('shop_id', $buyersShopId);
        }

        return $query;
    }
}
