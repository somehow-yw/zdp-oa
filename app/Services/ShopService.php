<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/4
 * Time: 19:56
 */

namespace App\Services;

use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;

use App\Repositories\Shops\Contracts\ShopRepository;
use Zdp\Main\Data\Models\DpGoodsSigning;
use Zdp\Main\Data\Models\DpShopInfo;
use Zdp\Main\Data\Models\DpShopRankRule;
use Zdp\Search\Services\ElasticService;
use App\Repositories\Buyers\Contracts\GradeRepositoryInterface;
use App\Repositories\Buyers\Contracts\SupplierRepositoryInterface;

class ShopService
{
    private $httpRequest;
    private $mainSignKey;
    private $mainRequestUrl;
    private $shopRepo;

    public function __construct(
        HTTPRequestUtil $httpRequest,
        ShopRepository $shopRepo
    ) {
        $this->httpRequest = $httpRequest;
        $this->mainSignKey = config('signature.main_sign_key');
        $this->mainRequestUrl = config('request_url.main_request_url');
        $this->shopRepo = $shopRepo;
    }

    /**
     * 店铺类型列表
     *
     * @return string
     */
    public function getShopTypeList()
    {
        $requestDataArr = [
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $this->mainSignKey);

        $requestUrl = $this->mainRequestUrl . '/shop/type/list';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->get($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }

    /**
     * 店铺类型信息（和店铺类型列表一样，只是键名变更了）
     *
     * @return string
     */
    public function getShopTypeInfo()
    {
        $requestDataArr = [
            'remark' => '找冻品OA系统请求',
        ];
        $signRequestDataArr = RequestDataEncapsulationUtil::requestDataSign($requestDataArr, $this->mainSignKey);

        $requestUrl = $this->mainRequestUrl . '/shop/type/info';
        $headersArr = [
            'Accept' => 'application/json',
        ];
        $reData = $this->httpRequest->get($requestUrl, $signRequestDataArr, $headersArr);

        return $reData;
    }

    /**
     * 商品列表中获得商品所属店铺信息
     *
     * @param $shopId int 店铺ID
     *
     * @return array
     */
    public function getShopInfoFromGoodsList($shopId)
    {
        $columnSelectArr = [
            'shop'   => [
                'dianPuName as shop_name',
                'jieDanTel as order_receive_tel',
                'dianpuJianJie as business_types',
                'xiangXiDiZi as shop_address',
                'signing_type',
                'signing_goods_num',
            ],
            'user'   => ['lianxiTel as boos_tel'],
            'market' => ['pianqu as market_name'],
        ];
        $shopInfoObj = $this->shopRepo->getShopInfo($shopId, $columnSelectArr);
        $reDataArr = new \stdClass();
        if (!is_null($shopInfoObj)) {
            $signingNum = 0;
            if ($shopInfoObj->signing_type != DpShopInfo::NOT_SIGNING) {
                // 店铺已签约商品数
                $signingNum = DpGoodsSigning::query()->where('shop_id', $shopId)->count();
            }
            $userObj = $shopInfoObj->user[0];
            $marketObj = $shopInfoObj->market;
            $reDataArr = [
                'shop_name'         => $shopInfoObj->shop_name,
                'market_name'       => empty($marketObj->market_name) ? '' : $marketObj->market_name,
                'order_receive_tel' => $shopInfoObj->order_receive_tel,
                'boos_tel'          => empty($userObj->boos_tel) ? '' : $userObj->boos_tel,
                'business_types'    => $shopInfoObj->business_types,
                'shop_address'      => $shopInfoObj->shop_address,
                'signing_type'      => $shopInfoObj->signing_type,
                'max_signing_num'   => $shopInfoObj->signing_goods_num,
                'signing_num'       => $signingNum,
            ];
        }

        return $reDataArr;
    }

    /**
     * 取得有待审核商品的店铺及待审核商品数量
     *
     * @param $marketId int 市场ID
     *
     * @return array
     */
    public function getShopInfoByMarket($marketId)
    {
        $columnSelectArr = [
            'aggregation' => ['count(*) as new_goods_number'],
            'goods'       => [],
            'shop'        => ['shopId as shop_id', 'dianPuName as shop_name'],
        ];

        return $this->shopRepo->getShopInfoByMarket($marketId, $columnSelectArr);
    }

    /**
     * 修改店铺等级分数，同时写入对应的日志
     *
     * @param int    $shopId       店铺ID
     * @param int    $scoreNo      分数操作类型
     * @param int    $operatorId   操作者ID
     * @param int    $operatorType 操作者类型
     * @param string $remark       操作备注
     *
     * @return array
     * @throws ShopException
     */
    public function updateShopScore($shopId, $scoreNo, $operatorId, $operatorType, $remark)
    {
        // 判断店铺是否存在
        $shopInfo = $this->shopRepo->getShop($shopId);
        if (!$shopInfo) {
            throw new \Exception('店铺不存在');
        }
        // 取得对应的操作分数规则
        $shopScoreOperatorRule = $this->shopRepo->getShopScoreOperatorRule($scoreNo);

        // 根据规则算出操作分数
        if ($shopScoreOperatorRule->max_score > 0) {
            $shopOperatorScore = mt_rand($shopScoreOperatorRule->small_score, $shopScoreOperatorRule->max_score);
        } else {
            $shopOperatorScore = $shopScoreOperatorRule->small_score;
        }
        if ($shopScoreOperatorRule->biggest_score > 0) {
            // 获取当前会员此类型下已获得分数
            $existinScore = $this->shopRepo->getShopScoreTypeExistingScore($shopId, $scoreNo);
            if ($existinScore >= $shopScoreOperatorRule->biggest_score) {
                // 如果已达到封顶分数，则不操作
                return [
                    'code'    => 0,
                    'message' => '已达封顶分数',
                    'data'    => [
                        'score' => 0,
                    ],
                ];
            } elseif (($existinScore + $shopOperatorScore) > $shopScoreOperatorRule->biggest_score) {
                // 如果添加后超过封顶分数，则调整操作分数
                $shopOperatorScore = $shopScoreOperatorRule->biggest_score - $existinScore;
            }
        }
        // 计算店铺等级
        $this->updateShopRank($shopId, $shopOperatorScore, $scoreNo, $operatorId, $operatorType, $remark);

        return [
            'code'    => 0,
            'message' => '操作成功',
            'data'    => [
                'score' => $shopOperatorScore,
            ],
        ];
    }

    /**
     * 店铺分及等级的处理
     *
     * @param $shopId       integer 店铺ID
     * @param $score        integer 变化分数
     * @param $scoreNo      integer 分类编号
     * @param $operatorId   integer 操作者ID
     * @param $operatorType integer 操作来源
     * @param $remark       string 备注
     */
    public function updateShopRank($shopId, $score, $scoreNo, $operatorId, $operatorType, $remark = '')
    {
        // 取出当前会员已有的分数
        $shopScoreInfoObj = $this->shopRepo->getShopScore($shopId);
        // 计算店铺等级
        $shopRankScore = $score + $shopScoreInfoObj->rank_score;
        $shopRank = $this->calculateShopRank($shopRankScore, $shopScoreInfoObj->shop_rank, $shopId);

        // 进行等级分的操作并记录日志
        $this->shopRepo->updateShopScore(
            $shopId,
            $scoreNo,
            $shopRankScore,
            $score,
            $shopRank,
            $operatorId,
            $operatorType,
            $remark
        );
    }

    /**
     * 计算当前店铺等级
     *
     * @param int $shopRankScore 店铺总分数
     * @param int $shopRank      当前店铺等级
     * @param int $shopId        店铺ID
     *
     * @return int
     */
    public function calculateShopRank($shopRankScore, $shopRank = 0, $shopId = 0)
    {
        if ($shopRankScore < 1) {
            return 1;
        }
        // 取得店铺分数对应的等级
        $rankInfo = $this->shopRepo->getShopRank($shopRankScore);
        // 当有店铺等级变化时，进行日志记录
        if ($shopRank > 0 && $shopId > 0 && $rankInfo->rank_no != $shopRank) {
            $createArr = [
                'shop_id'  => $shopId,
                'old_rank' => $shopRank,
                'new_rank' => $rankInfo->rank_no,
            ];
            DpShopRankRule::create($createArr);
            // 更新搜索Elastic的店铺信息
            /** @var ElasticService $elastic */
            $elastic = app()->make(ElasticService::class);
            $elastic->updateShop($shopId);
            // 更新缓存
            /** @var SupplierRepositoryInterface $supplierRepo */
            $supplierRepo = app()->make(SupplierRepositoryInterface::class);
            $supplierRepo->setCache($shopId);
            /** @var GradeRepositoryInterface $grade */
            $grade = app()->make(GradeRepositoryInterface::class);
            $grade->setCache($shopId);
        }

        return $rankInfo->rank_no;
    }
}
