<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/8/16
 * Time: 15:23
 */

namespace App\Services\External\Ious;

use App\Repositories\Ious\Contracts\XimuIousRepository;
use App\Utils\MoneyUnitConvertUtil;
use Zdp\Main\Data\Models\DpIousWhiteList;

/**
 * 徙木白条管理
 * Class XimuIousService
 * @package App\Services\External\Ious
 */
class XimuIousService
{
    // 前端支付状态
    const NOT_PAY = 1; // 未支付
    const PAY = 2;     // 已支付

    private $ximuIousRepo;

    public function __construct(XimuIousRepository $ximuIousRepo)
    {
        $this->ximuIousRepo = $ximuIousRepo;
    }

    /**
     * 徙木冻品贷白名单列表数据
     *
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/ious-manage
     *
     * @param       $page
     * @param       $size
     * @param array $queryArr 查询条件
     *                        ['mobile'=>'','province_id'=>null,'open_status'=>null,'pay_status'=>null]
     *                        参见请求Controller及接口文档
     *
     * @return array
     */
    public function getList($page, $size, array $queryArr)
    {
        $infos = $this->ximuIousRepo->getList($size, $queryArr);
        $reArr = [
            'page'  => (int)$page,
            'total' => $infos->total(),
            'lists' => [],
        ];
        if ($infos->count()) {
            // 有数据
            $listArr = [];
            foreach ($infos as $item) {
                if (empty($item->shop_name)) {
                    continue;
                }
                $totalLimit = $item->status == DpIousWhiteList::NOT_OPEN ? 0 : $item->total_limit;
                $payStatus = empty($item->apply_amount) ? self::NOT_PAY : self::PAY;
                $listArr[] = [
                    'shop_name'     => $item->shop_name,
                    'area_province' => $item->province,
                    'mobile'        => $item->mobile,
                    'open_status'   => $item->status,
                    'total_limit'   => MoneyUnitConvertUtil::fenToYuan($totalLimit),
                    'pay_status'    => $payStatus,
                    'pay_money'     => empty($item->apply_amount)
                        ? 0
                        : MoneyUnitConvertUtil::fenToYuan($item->apply_amount),
                ];
            }
            $reArr['lists'] = $listArr;
        }

        return $reArr;
    }
}
