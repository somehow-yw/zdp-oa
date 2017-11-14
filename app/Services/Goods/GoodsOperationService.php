<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/10/14
 * Time: 11:45
 */

namespace App\Services\Goods;

use App;
use DB;
use Event;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Zdp\Main\Data\Models\DpGoodsSigning;
use Zdp\Search\Services\ElasticService;

use App\Exceptions\AppException;
use App\Exceptions\Goods\GoodsException;

use App\Jobs\SendGoodsOperateNotice;

use App\Models\DpBrands;
use App\Models\DpGoodsBasicAttribute;
use App\Models\DpGoodsInfo;
use App\Models\DpGoodsPic;
use App\Models\DpGoodsType;
use App\Models\User;
use App\Models\DpGoodsInspectionReport;

use App\Repositories\Goods\Contracts\GoodsOperationRepository;
use App\Repositories\Goods\Contracts\GoodsRepository;
use App\Repositories\Goods\Contracts\GoodsTypeRepository;
use App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository;
use App\Repositories\Goods\Contracts\GoodsConstraintsRepository;

use LogPusher\Events\GoodsStatusWasUpdated;
use LogPusher\Events\GoodsWasDeleted;
use LogPusher\Events\GoodsWasUnDeleted;
use LogPusher\Events\GoodsWasUpdated;
use LogPusher\Models\DpGoodsOperationLog;

/**
 * Class GoodsOperationService.
 * 商品操作的处理 不包括添加 修改
 *
 * @package App\Services\Goods
 */
class GoodsOperationService
{
    use DispatchesJobs;

    private $goodsOperationRepo;
    private $goodsTypeRepo;
    /** @var User $user */
    private $user;

    public function __construct(
        GoodsOperationRepository $goodsOperationRepo,
        GoodsTypeRepository $goodsTypeRepo,
        Guard $auth
    ) {
        $this->goodsOperationRepo = $goodsOperationRepo;
        $this->goodsTypeRepo = $goodsTypeRepo;
        $this->user = $auth->user();
    }

    /**
     * 商品图片的删除
     *
     * @param $pictureId int 图片ID
     *
     * @return void
     */
    public function delGoodsPicture($pictureId)
    {
        $selectArr = ['goodsid as goods_id'];
        $goodsPicCollect = DpGoodsPic::getPicInfoById($pictureId, $selectArr);
        $this->goodsOperationRepo->delGoodsPicture($pictureId);
        // 进行商品搜索索引更新
        $this->updateElasticIndex([$goodsPicCollect->goods_id]);
    }

    /**
     * 商品检验报告图片的删除
     *
     * @param $pictureId int 图片ID
     *
     * @return void
     */
    public function delGoodsInspectionReport($pictureId)
    {
        $selectArr = ['goods_id'];
        $goodsInspectionReportCollect = DpGoodsInspectionReport::getPicInfoById($pictureId, $selectArr);
        $this->goodsOperationRepo->delGoodsInspectionReport($pictureId);
        // 进行商品搜索索引更新
        $this->updateElasticIndex([$goodsInspectionReportCollect->goods_id]);
    }

    /**
     * 商品搜索索引更新
     *
     * @param array $goodsIdArr array 商品ID 格式:[1,2,3,...]
     *
     * @return void
     */
    private function updateElasticIndex(array $goodsIdArr)
    {
        /** @var ElasticService $elasticIndexUpdateObj */
        $elasticIndexUpdateObj = App::make(ElasticService::class);
        $elasticIndexUpdateObj->updateGoods($goodsIdArr);
    }

    /**
     * 下架普通商品
     *
     * @param $goodsId       integer 商品id
     * @param $soldOutReason string 下架原因
     * @param $noticeWay     integer 通知方式
     */
    public function soldOutOrdinaryGoods($goodsId, $soldOutReason, $noticeWay)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId, $soldOutReason, $noticeWay) {
                $goods = DpGoodsInfo::find($goodsId);
                $formerAuditStatus = $goods->shenghe_act;
                $self->goodsOperationRepo->soldOutOrdinaryGoods($goodsId);
                Event::fire(
                    'goods.operate.update.status',
                    new GoodsStatusWasUpdated(
                        DpGoodsInfo::find($goodsId),
                        $self->user,
                        DpGoodsOperationLog::ADMIN,
                        DpGoodsOperationLog::STOP_SALE_GOODS,
                        $formerAuditStatus,
                        $soldOutReason
                    )
                );
                dispatch(new SendGoodsOperateNotice($goodsId, $soldOutReason, $noticeWay));
            }
        );
    }

    /**
     * 刷新普通商品价格
     *
     * @param $goodsId integer 商品id
     */
    public function refreshOrdinaryGoodsPrice($goodsId)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId) {
                $goods = DpGoodsInfo::find($goodsId);
                $formerAuditStatus = $goods->shenghe_act;
                $self->goodsOperationRepo->refreshOrdinaryGoodsPrice($goodsId);
                Event::fire(
                    'goods.operate.refresh.price',
                    new GoodsWasUpdated(
                        DpGoodsInfo::find($goodsId),
                        $self->user,
                        DpGoodsOperationLog::ADMIN,
                        DpGoodsOperationLog::REFRESH_PRICE_GOODS,
                        $formerAuditStatus
                    )
                );
            }
        );
    }

    /**
     * 删除普通商品
     *
     * @param $goodsId      integer 商品id
     * @param $deleteReason string  删除原因
     * @param $noticeWay    integer 通知方式
     */
    public function deleteOrdinaryGoods($goodsId, $deleteReason, $noticeWay)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId, $deleteReason, $noticeWay) {
                $goods = DpGoodsInfo::find($goodsId);
                $formerAuditStatus = $goods->shenghe_act;
                if (DpGoodsInfo::STATUS_DEL != $goods->shenghe_act) {
                    // 删除商品
                    $self->goodsOperationRepo->deleteOrdinaryGoods($goodsId);
                    // 删除商品的签约
                    DpGoodsSigning::query()
                        ->where('goods_id', $goodsId)
                        ->delete();
                    Event::fire(
                        'goods.operate.delete',
                        new GoodsWasDeleted(
                            $goods,
                            $self->user,
                            DpGoodsOperationLog::ADMIN,
                            DpGoodsOperationLog::DELETE_GOODS,
                            $formerAuditStatus,
                            $deleteReason
                        )
                    );
                    dispatch(new SendGoodsOperateNotice($goodsId, $deleteReason, $noticeWay));
                }
            }
        );
    }

    /**
     * 恢复删除商品
     *
     * @param $goodsId integer 商品id
     */
    public function unDeleteOrdinaryGoods($goodsId)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId) {
                $goods = DpGoodsInfo::find($goodsId);
                $formerAuditStatus = $goods->shenghe_act;
                // 只有删除状态的商品才能恢复删除
                if (DpGoodsInfo::STATUS_DEL == $goods->shenghe_act) {
                    $self->goodsOperationRepo->unDeleteOrdinaryGoods($goodsId);
                    Event::fire(
                        'goods.operate.undelete',
                        new GoodsWasUnDeleted(
                            $goods,
                            $self->user,
                            DpGoodsOperationLog::ADMIN,
                            DpGoodsOperationLog::DELETE_TO_READY_AUDIT,
                            $formerAuditStatus
                        )
                    );
                }
            }
        );
    }

    /**
     * 上架普通商品
     *
     * @param $goodsId integer 商品id
     */
    public function onSaleOrdinaryGoods($goodsId)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId) {
                /** @var DpGoodsInfo $goods */
                $goods = DpGoodsInfo::find($goodsId);
                $price = $goods->goodsAttribute()->first()->goods_price;
                $minPrice = DpGoodsBasicAttribute::GOODS_MIN_PRICE;
                if ($price <= $minPrice) {
                    throw new AppException("上架商品价格不能小于等于{$minPrice}", 101);
                }
                $formerAuditStatus = $goods->shenghe_act;
                $self->goodsOperationRepo->onSaleOrdinaryGoods($goodsId);
                Event::fire(
                    'goods.operate.update.status',
                    new GoodsStatusWasUpdated(
                        DpGoodsInfo::find($goodsId),
                        $self->user,
                        DpGoodsOperationLog::ADMIN,
                        DpGoodsOperationLog::SALE_GOODS,
                        $formerAuditStatus
                    )
                );
            }
        );
    }

    /**
     * 商品审核通过处理
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     * @throws GoodsException
     */
    public function auditPass($goodsId)
    {
        // 进行审核前商品信息验证
        $verifyResult = $this->goodsInfoVerify($goodsId);
        if (!$verifyResult) {
            throw new GoodsException(GoodsException::GOODS_INFO_ERROR);
        }
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId) {
                $goods = DpGoodsInfo::find($goodsId);
                $formerAuditStatus = $goods->shenghe_act;
                $self->goodsOperationRepo->auditPass($goodsId);
                Event::fire(
                    'goods.operate.update.status',
                    new GoodsStatusWasUpdated(
                        DpGoodsInfo::find($goodsId),
                        $self->user,
                        DpGoodsOperationLog::ADMIN,
                        DpGoodsOperationLog::AUDIT_GOODS,
                        $formerAuditStatus
                    )
                );
            }
        );
    }

    /**
     * 审核拒绝处理
     *
     * @param $goodsId       int 商品ID
     * @param $refusedReason string 拒绝理由
     * @param $noticeWay     int 拒绝原因通知卖家的类型 0=不进行通知 1=微信通知 2=短信通知
     *
     * @return void
     */
    public function auditRefused($goodsId, $refusedReason, $noticeWay)
    {
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $goodsId, $refusedReason, $noticeWay) {
                $goods = DpGoodsInfo::find($goodsId);
                $formerAuditStatus = $goods->shenghe_act;
                $updateNum = $self->goodsOperationRepo->auditRefused($goodsId, $refusedReason);
                // 进行队列操作发送拒绝理由
                if ($updateNum) {
                    // 商品审核拒绝事件触发
                    Event::fire(
                        'goods.operate.update.status',
                        new GoodsStatusWasUpdated(
                            DpGoodsInfo::find($goodsId),
                            $self->user,
                            DpGoodsOperationLog::ADMIN,
                            DpGoodsOperationLog::REFUSED_GOODS,
                            $formerAuditStatus,
                            $refusedReason
                        )
                    );
                    if ($noticeWay) {
                        $self->dispatch(new SendGoodsOperateNotice($goodsId, $refusedReason, $noticeWay));
                    }
                }
            }
        );
    }

    /**
     * 根据商品分类ID将商品状态更改为待审核
     *
     * @param $goodsTypeId int 商品分类ID
     *
     * @return int 影响的行数
     */
    public function updateGoodsStatusToNotAudit($goodsTypeId)
    {
        $this->goodsOperationRepo->updateGoodsStatusToNotAudit($goodsTypeId);
    }

    /**
     * 商品信息验证
     *
     * @param $goodsId int 商品ID
     *
     * @return boolean
     * @throws GoodsException
     */
    private function goodsInfoVerify($goodsId)
    {
        // 取出商品需要验证的信息
        /** @var GoodsRepository $goodsRepo */
        $goodsRepo = App::make(GoodsRepository::class);
        $columnSelectArr = [
            'goods'                 => ['goods_type_id', 'brand_id', 'smuggle'],
            'goodsAttribute'        => [
                'specs',
                'types',
                'meter_unit',
                'goods_price',
                'inventory',
                'tag',
            ],
            'specialAttribute'      => ['propeid', 'attr_value', 'prope_name'],
            'goodsPicture'          => ['picid'],
            'goodsInspectionReport' => ['id'],
            'goodsPriceRule'        => [],
        ];
        $goodsInfoCollect = $goodsRepo->getGoodsInfo($goodsId, $columnSelectArr);
        // 验证基本信息
        if (is_null($goodsInfoCollect)) {
            throw new GoodsException(GoodsException::GOODS_NO);
        } elseif (empty(DpGoodsInfo::getSmuggleName($goodsInfoCollect->smuggle))) {
            // 国别
            throw new GoodsException(GoodsException::GOODS_SMUGGLE_ERROR);
            // return false;
        } elseif (is_null(DpGoodsType::find($goodsInfoCollect->goods_type_id))) {
            // 商品分类
            throw new GoodsException(GoodsException::GOODS_TYPE_NO);
            // return false;
        } elseif (is_null(DpBrands::find($goodsInfoCollect->brand_id))) {
            // 商品品牌
            throw new GoodsException(GoodsException::GOODS_BRAND_NO);
            // return false;
        }
        // 验证基本属性
        $goodsAttributeCollect = $goodsInfoCollect->goodsAttribute;
        if (100 === DpGoodsBasicAttribute::getGoodsUnitName($goodsAttributeCollect->meter_unit)) {
            // 商品计量单位
            throw new GoodsException(GoodsException::GOODS_UNIT_ERROR);
            // return false;
        } elseif (empty($goodsAttributeCollect->goods_price)
                  || $goodsAttributeCollect->goods_price <= DpGoodsBasicAttribute::GOODS_MIN_PRICE
        ) {
            // 商品价格
            throw new GoodsException(GoodsException::GOODS_PRICE_ERROR);
            // return false;
        } elseif ($goodsAttributeCollect->inventory < 0) {
            // 商品库存量
            throw new GoodsException(GoodsException::GOODS_INVENTORY_ERROR);
            // return false;
        }
        // 取出基本属性进行验证
        /** @var GoodsConstraintsRepository $goodsConstraintsRepo */
        $goodsConstraintsRepo = App::make(GoodsConstraintsRepository::class);
        $goodsConstraintsArr = $goodsConstraintsRepo->getGoodsBasicAttr($goodsInfoCollect->goods_type_id);
        $typeConstraintArr = $goodsConstraintsArr['type_constraint']->toArray();
        $specConstraintArr = $goodsConstraintsArr['spec_constraint']->toArray();
        // 商品保存着的规格及型号
        $goodsSpecAttrArr = json_decode($goodsAttributeCollect->specs, true);
        $goodsTypeAttrArr = json_decode($goodsAttributeCollect->types, true);
        if ($goodsSpecAttrArr['constraint_id'] != $specConstraintArr['attribute_id'] ||
            $goodsSpecAttrArr['format_type_id'] != $specConstraintArr['format_type_id']
        ) {
            // 规格错误
            throw new GoodsException(GoodsException::GOODS_SPEC_ERROR);
            // return false;
        } elseif ($goodsTypeAttrArr['constraint_id'] != $typeConstraintArr['attribute_id'] ||
                  $goodsTypeAttrArr['format_type_id'] != $typeConstraintArr['format_type_id']
        ) {
            // 型号错误
            throw new GoodsException(GoodsException::GOODS_TYPE_ERROR);
            // return false;
        }
        // 规格中每一个选项的验证
        $returnStatus = $this->verifyConstraintOption($goodsSpecAttrArr, $specConstraintArr);
        if (!$returnStatus) {
            throw new GoodsException(GoodsException::GOODS_SPEC_OPTION_ERROR);
            // return false;
        }
        // 型号中每一个选项的验证
        $returnStatus = $this->verifyConstraintOption($goodsTypeAttrArr, $typeConstraintArr);
        if (!$returnStatus) {
            throw new GoodsException(GoodsException::GOODS_TYPE_OPTION_ERROR);
            // return false;
        }
        // 取出特殊属性进行验证
        $goodsSpecAttributeCollect = $goodsInfoCollect->specialAttribute;
        /** @var GoodsTypeSpecialAttrRepository $goodsTypeSpecialAttrRepo */
        $goodsTypeSpecialAttrRepo = App::make(GoodsTypeSpecialAttrRepository::class);
        $columnSelectArr = ['attribute_name', 'format_type_id', 'format_values'];
        foreach ($goodsSpecAttributeCollect as $goodsSpecAttr) {
            $specAttrId = $goodsSpecAttr->propeid;
            $goodsSpecialAttrCollect = $goodsTypeSpecialAttrRepo->getConstraintInfoById($specAttrId, $columnSelectArr);
            if (is_null($goodsSpecialAttrCollect)) {
                throw new GoodsException([], "特殊属性{$goodsSpecAttr->prope_name}已不存在");
                // return false;
            }
            $goodsAttrValueArr = json_decode($goodsSpecAttr->attr_value, true);
            if ($goodsAttrValueArr['format_type_id'] != $goodsSpecialAttrCollect->format_type_id) {
                throw new GoodsException([], "特殊属性{$goodsSpecAttr->prope_name}规则已变化");
                // return false;
            }
            $attrValueArr = [];
            $attrValueArr['format_values'] = json_decode($goodsSpecialAttrCollect->format_values, true);
            $returnStatus = $this->verifyConstraintOption($goodsAttrValueArr, $attrValueArr);
            if (!$returnStatus) {
                throw new GoodsException([], "特殊属性{$goodsSpecAttr->prope_name}选项已变化");
                // return false;
            }
        }
        // 验证图片数量
        $minPicNum = DpGoodsPic::MIN_PIC_NUM;
        if (count($goodsInfoCollect->goodsPicture) < $minPicNum) {
            throw new GoodsException([], "图片数量最少需要{$minPicNum}张");
            // return false;
        }

        return true;
    }

    /**
     * 验证商品属性约束是否正确
     *
     * @param array $goodsAttrArr  array 商品中的约束
     * @param array $constraintArr array 规则中的约束
     *
     * @return bool
     */
    private function verifyConstraintOption(array $goodsAttrArr, array $constraintArr)
    {
        $returnStatus = false;
        switch ($goodsAttrArr['format_type_id']) {
            case 1:
                // 文本框
                if ('string' === $constraintArr['format_values'][0]['rule']) {
                    $returnStatus = true;
                } elseif ($constraintArr['format_values'][0]['unit'] === $goodsAttrArr['values'][0]['unit']) {
                    $returnStatus = true;
                }
                break;
            case 2:
                // 单选
                $goodsAttrValue = $goodsAttrArr['values'][0]['value'];
                $constraintValueArr = array_pluck($constraintArr['format_values'], 'value');
                if (in_array($goodsAttrValue, $constraintValueArr)) {
                    $returnStatus = true;
                }
                break;
            case 3:
                // 多选
                $constraintValueArr = array_pluck($constraintArr['format_values'], 'value');
                $goodsAttrValueArr = array_pluck($goodsAttrArr['values'], 'value');
                $diffArr = array_diff($goodsAttrValueArr, $constraintValueArr);
                if (count($diffArr) === 0) {
                    $returnStatus = true;
                }
                break;
            case 4:
            case 5:
                // X-Y区间 X*Y值
                $constraintValueArr = array_pluck($constraintArr['format_values'], 'unit');
                $goodsAttrValueArr = array_pluck($goodsAttrArr['values'], 'unit');
                $diffArr = array_diff($goodsAttrValueArr, $constraintValueArr);
                if (count($diffArr) === 0) {
                    $returnStatus = true;
                }
                break;
        }

        return $returnStatus;
    }
}
