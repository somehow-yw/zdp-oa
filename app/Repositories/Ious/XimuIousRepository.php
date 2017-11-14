<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/8/16
 * Time: 15:26
 */

namespace App\Repositories\Ious;

use App\Repositories\Ious\Contracts\XimuIousRepository as RepositoriesContract;
use Elastica\Transport\Null;
use Zdp\Main\Data\Models\DpIousWhiteList;

class XimuIousRepository implements RepositoriesContract
{
    /**
     * 获取徙木冻品贷白名单
     *
     * @param       $size     integer 每页获取数量
     * @param array $queryArr 获取数据的条件 格式：
     *                        ['mobile'=>'','province_id'=>null,'open_status'=>null,'pay_status'=>null]
     *                        只支持以上查询条件，如加入新的条件，请更改以上内容
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($size, $queryArr)
    {
        $selectArr = [
            'shops.dianPuName as shop_name',
            'shops.province',
            'iws.mobile',
            'iws.status',
            'iws.total_limit',
            'ipays.apply_amount',
        ];
        $query = DpIousWhiteList::from('dp_ious_white_lists as iws')
            ->leftJoin('dp_ious_pay_statistics as ipays', 'iws.shop_id', '=', 'ipays.shop_id')
            ->leftJoin('dp_shopInfo as shops', 'iws.shop_id', '=', 'shops.shopId')
            ->select($selectArr)
            ->withTrashed()
            ->WhereNull('iws.deleted_at');
        if (!empty($queryArr['mobile'])) {
            $query = $query->where('iws.mobile', $queryArr['mobile']);
        } else {
            if (!is_null($queryArr['province_id'])) {
                // 省份ID
                $query = $query->where('iws.province_id', $queryArr['province_id']);
            }
            if (!empty($queryArr['open_status'])) {
                // 开通状态
                $query = $query->where('iws.status', $queryArr['open_status']);
            }
            if (!empty($queryArr['pay_status'])) {
                if ($queryArr['pay_status'] == 2) {
                    // 支付状态(已支付)
                    $query = $query->where('ipays.apply_amount', '>', 0);
                } elseif ($queryArr['pay_status'] == 1) {
                    // 支付状态(未支付)
                    $query = $query->WhereNull('ipays.apply_amount');
                }
            }
        }

        return $query->paginate($size);
    }
}
