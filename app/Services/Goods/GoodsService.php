<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/20/16
 * Time: 6:29 PM
 */

namespace App\Services\Goods;

use App\Models\DpPriceRule;
use DB;
use App;
use Event;
use Illuminate\Contracts\Auth\Guard;

use App\Repositories\Goods\Contracts\GoodsRepository;
use App\Repositories\Goods\Contracts\GoodsTypeRepository;
use App\Repositories\Shops\Contracts\MarketRepository;
use App\Repositories\Goods\Contracts\BrandsRepository;
use App\Repositories\Goods\Contracts\GoodsConstraintsRepository;
use App\Repositories\Goods\Contracts\GoodsTypeSpecialAttrRepository;
use App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository;

use App\Models\DpGoodsInfo;
use App\Models\DpGoodsOperationLog;
use App\Models\User;
use App\Models\DpGoodsBasicAttribute;

use App\Services\Goods\Traits\AttrConvertTrait;

use LogPusher\Events\GoodsInfoWasUpdated;

class GoodsService
{
    use AttrConvertTrait;

    private $goodsRepo;
    private $goodsTypeRepo;

    public function __construct(GoodsRepository $goodsRepo, GoodsTypeRepository $goodsTypeRepo)
    {
        $this->goodsRepo = $goodsRepo;
        $this->goodsTypeRepo = $goodsTypeRepo;
    }

    /**
     * 旧商品图片（包括检验报告）获取
     *
     * @param $goodsId int 商品ID
     *
     * @return array
     */
    public function getOldGoodsPicture($goodsId)
    {
        $columnSelectArr = [
            'goods'        => ['inspection_report'],
            'goodsPicture' => ['picid as id', 'ypic_path as picture_add', 'ordernum as sort_value'],
        ];
        $pictureObj = $this->goodsRepo->getOldGoodsPicture($goodsId, $columnSelectArr);

        $rePictureArr = [
            'pictures'          => [],
            'inspection_report' => [],
        ];
        if (!is_null($pictureObj)) {
            if (!empty($pictureObj->inspection_report)) {
                $rePictureArr['inspection_report'][] = [
                    'picture_id'  => 0,
                    'picture_add' => $pictureObj->inspection_report,
                    'sort_value'  => 1,
                ];
            }
            foreach ($pictureObj->goodsPicture as $picture) {
                $rePictureArr['pictures'][] = [
                    'picture_id'  => $picture->id,
                    'picture_add' => $picture->picture_add,
                    'sort_value'  => $picture->sort_value,
                ];
            }
        }

        return $rePictureArr;
    }

    /**
     * 商品添加，目前主要是做为旧商品的迁移。如果做为新商品的添加需要再按业务需求进行重构
     *
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/goods-add-manage 参数具体说明
     *
     * @param array $basicInfos        array 商品基本信息
     * @param array $basicAttributes   array 商品基本属性
     * @param array $specialAttributes array 商品特殊属性
     * @param array $pictures          array 商品图片
     * @param array $inspectionReports array 检验报告图片
     * @param       $adminId           int 管理员ID
     */
    public function addGoods(
        array $basicInfos,
        array $basicAttributes,
        array $specialAttributes,
        array $pictures,
        array $inspectionReports,
        $adminId
    ) {
        // 取得旧商品信息
        $oldGoodsInfoObj = $this->getOldGoodsInfo($basicInfos['goods_id']);
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use (
                $self,
                $basicInfos,
                $basicAttributes,
                $specialAttributes,
                $pictures,
                $inspectionReports,
                $adminId,
                $oldGoodsInfoObj
            ) {
                // 生成商品组编号 会员ID 添加时间
                $goodsAddTime = strtotime($oldGoodsInfoObj->addtime);
                $groupNumber = $oldGoodsInfoObj->shid . $goodsAddTime;
                // ========================
                // 商品基本信息保存
                // ========================
                $pictureCount = count($pictures);
                $addBasicInfoArr = $self
                    ->genGoodsAddInfo($basicInfos, $groupNumber, $oldGoodsInfoObj, $adminId, $pictureCount);
                $goodsBasicInfoObj = $self->goodsRepo->addBasicInfo($addBasicInfoArr);
                // ========================
                // 商品基本属性保存
                // ========================
                $oldGoodsAttribute = $oldGoodsInfoObj->goodsAttribute;
                $addBasicAttrArr = $self->genGoodsBasicAttrInfo(
                    $oldGoodsAttribute,
                    $basicAttributes,
                    $groupNumber,
                    $basicInfos['goods_id']
                );
                $self->goodsRepo->addBasicAttr($addBasicAttrArr);
                // ========================
                // 商品特殊属性保存
                // ========================
                $addSpecialAttrArr = $self->genSpecialAttrInfo($specialAttributes, $basicInfos['goods_id']);
                $self->goodsRepo->addSpecialAttr($addSpecialAttrArr, $basicInfos['goods_id']);
                // ========================
                // 商品图片保存或修改
                // ========================
                $pictureInfoArr = $self->genPictureInfo($pictures, $basicInfos['goods_id']);
                $self->goodsRepo->addOrUpdatePicture($pictureInfoArr);
                // ========================
                // 商品检验报告图片保存或修改
                // ========================
                if (count($inspectionReports)) {
                    $inspectionReportInfoArr =
                        $self->genInspectionReportInfo($inspectionReports, $basicInfos['goods_id']);
                    $self->goodsRepo
                        ->addOrUpdateInspectionReport($inspectionReportInfoArr, $basicInfos['goods_id']);
                }
                // ========================
                // 更改市场下的商品数量 1=增加的数量
                // ========================
                /** @var  $marketRepo MarketRepository */
                $marketRepo = App::make(MarketRepository::class);
                $marketRepo->updateGoodsNumber($oldGoodsInfoObj->shop->pianquId, 1);
                // ========================
                // 更改分类下的商品数量 1=增加的数量
                // ========================
                $self->goodsTypeRepo->updateGoodsTypeGoodsNumber($basicInfos['goods_type_id'], 1);
                // ========================
                // 更改旧商品表的转移状态
                // ========================
                $self->goodsRepo->updateOldGoodsTransfer($basicInfos['goods_id']);
            }
        );
    }

    /**
     * 商品信息修改
     *
     * @link http://dev.idongpin.com/zdp-dev/zdp-oa/wikis/goods-add-manage 参数具体说明
     *
     * @param array $basicInfos        array 商品基本信息
     * @param array $basicAttributes   array 商品基本属性
     * @param array $specialAttributes array 商品特殊属性
     * @param array $pictures          array 商品图片
     * @param array $inspectionReports array 检验报告图片
     * @param array $priceRules        array 价格规则数组
     * @param       $adminId           int 管理员ID
     * @param       $adminTel          string 管理员联系电话
     */
    public function updateGoodsInfo(
        array $basicInfos,
        array $basicAttributes,
        array $specialAttributes,
        array $pictures,
        array $inspectionReports,
        array $priceRules,
        $adminId,
        $adminTel
    ) {
        // 取得修改前的商品信息
        $columnSelectArr = [
            'goods'                 => ['goods_type_id', 'shenghe_act', 'shopid'],
            'goodsAttribute'        => ['basicid', 'goods_price'],
            'specialAttribute'      => ['id'],
            'goodsPicture'          => ['picid as picture_id'],
            'goodsInspectionReport' => ['id as picture_id'],
            'goodsPriceRule'        => [],
        ];
        $oldGoodsInfoObj = $this->goodsRepo->getGoodsInfo($basicInfos['goods_id'], $columnSelectArr);
        $self = $this;
        DB::connection('mysql_zdp_main')->transaction(
            function () use (
                $self,
                $basicInfos,
                $basicAttributes,
                $specialAttributes,
                $pictures,
                $inspectionReports,
                $priceRules,
                $adminId,
                $adminTel,
                $oldGoodsInfoObj
            ) {
                $logRemark = '后端管理进行商品修改';
                /** @var $auth Guard */
                $auth = App::make(Guard::class);
                /** @var User $user */
                $user = $auth->user();
                $eventObj = new GoodsInfoWasUpdated(
                    DpGoodsInfo::find($basicInfos['goods_id']),
                    $user,
                    DpGoodsOperationLog::ADMIN,
                    DpGoodsOperationLog::UPDATE_GOODS,
                    $oldGoodsInfoObj->shenghe_act,
                    $logRemark
                );
                // ========================
                // 商品基本信息修改
                // ========================
                $pictureCount = count($pictures);
                $updateBasicInfoArr = $self
                    ->genGoodsUpdateInfo($basicInfos, $pictureCount, $oldGoodsInfoObj->shenghe_act);
                $self->goodsRepo->updateBasicInfo($updateBasicInfoArr, $basicInfos['goods_id']);
                // ========================
                // 商品基本属性修改
                // ========================
                $updateBasicAttrArr = $self->genGoodsBasicAttrUpdateInfo($basicAttributes);
                $self->goodsRepo->updateBasicAttr($updateBasicAttrArr, $oldGoodsInfoObj->goodsAttribute->basicid);
                // ========================
                // 商品特殊属性保存
                // ========================
                $addSpecialAttrArr = $self->genSpecialAttrInfo($specialAttributes, $basicInfos['goods_id']);
                $self->goodsRepo->addSpecialAttr($addSpecialAttrArr, $basicInfos['goods_id']);
                // ========================
                // 商品图片保存或修改
                // ========================
                $pictureInfoArr = $self->genPictureInfo($pictures, $basicInfos['goods_id']);
                $self->goodsRepo->addOrUpdatePicture($pictureInfoArr);
                // ===========================
                // 商品检验报告图片保存或修改
                // ===========================
                if (count($inspectionReports)) {
                    $inspectionReportInfoArr =
                        $self->genInspectionReportInfo($inspectionReports, $basicInfos['goods_id']);
                    $self->goodsRepo
                        ->addOrUpdateInspectionReport($inspectionReportInfoArr, $basicInfos['goods_id']);
                }
                // ===========================
                // 价格体系保存
                // ===========================
                if (count($priceRules)) {
                    $self->goodsRepo->updateGoodsPriceRule($basicInfos['goods_id'], $priceRules);
                }

                // ========================
                // 如果价格有变化
                // 更改商品的前一次价格并记录改价日志
                // ========================
                $originalGoodsPrice = $oldGoodsInfoObj->goodsAttribute->goods_price;    // 修改前价格
                if ($originalGoodsPrice != $basicAttributes['goods_price']) {
                    /** @var $goodsPriceChangeLogRepo GoodsPriceChangeLogRepository */
                    $goodsPriceChangeLogRepo = App::make(GoodsPriceChangeLogRepository::class);
                    $goodsPriceChangeLogRepo->addGoodsPriceLog(
                        $originalGoodsPrice,
                        $basicAttributes['goods_price'],
                        $basicInfos['goods_id'],
                        $oldGoodsInfoObj->goodsAttribute->basicid,
                        $adminId,
                        $adminTel,
                        $oldGoodsInfoObj->shopid
                    );
                    $logRemark .= '-价格有所改变';
                }
                // 操作日志及快照记录
                $self->logWrite($eventObj);
            }
        );
    }

    /**
     * 处理并返回商品详情信息
     *
     * @param $goodsId int 商品ID
     *
     * @return array
     */
    public function getGoodsInfo($goodsId)
    {
        $columnSelectArr = [
            'goods'                 => [
                'goods_type_id',
                'gname',
                'goods_title',
                'origin',
                'brand_id',
                'brand as brand_name',
                'shenghe_act',
                'halal',
                'smuggle',
                'jianjie',
            ],
            'goodsAttribute'        => [
                'basicid',
                'guigei',
                'specs',
                'xinghao',
                'types',
                'net_weight',
                'rough_weight',
                'meat_weight',
                'meter_unit',
                'goods_price',
                'inventory',
                'tag',
                'fromsell_num',
                'price_adjust_frequency',
            ],
            'specialAttribute'      => ['id', 'propeid', 'prope_value', 'attr_value',],
            'goodsPicture'          => ['picid as picture_id', 'ypic_path as picture_add', 'ordernum as sort_value'],
            'goodsInspectionReport' => ['id as picture_id', 'picture_add', 'sort_value'],
            'goodsPriceRule'        => ['price_rule_id', 'goods_id', 'buy_num', 'preferential'],
        ];
        $goodsInfoObj = $this->goodsRepo->getGoodsInfo($goodsId, $columnSelectArr);
        $goodsInfoArr = [];
        if (!is_null($goodsInfoObj)) {
            // 规格处理
            $specArr = json_decode($goodsInfoObj->goodsAttribute->specs, true);
            $specArr['attr_values'] = $goodsInfoObj->goodsAttribute->guigei;
            // 型号处理
            $typeArr = json_decode($goodsInfoObj->goodsAttribute->types, true);
            $typeArr['attr_values'] = $goodsInfoObj->goodsAttribute->xinghao;
            // 特殊属性处理
            $specialAttributeArr = [];
            if (count($goodsInfoObj->specialAttribute) > 0) {
                foreach ($goodsInfoObj->specialAttribute as $specialAttr) {
                    $specialAttrArr = json_decode($specialAttr->attr_value, true);
                    $specialAttrArr['attr_values'] = $specialAttr->prope_value;
                    $specialAttributeArr[] = $specialAttrArr;
                }
            }
            // 组织输出数据
            $goodsAttributeObj = $goodsInfoObj->goodsAttribute;
            $goodsInfoArr['activity_type'] = $goodsAttributeObj->tag;
            $goodsInfoArr['basic_infos'] = [
                'goods_id'      => $goodsInfoObj->id,
                'goods_name'    => $goodsInfoObj->gname,
                'goods_type_id' => $goodsInfoObj->goods_type_id,
                'brand_name'    => $goodsInfoObj->brand_name,
                'brand_id'      => $goodsInfoObj->brand_id,
                'origin'        => $goodsInfoObj->origin,
                'halal'         => $goodsInfoObj->halal,
                'smuggle_id'    => $goodsInfoObj->smuggle,
                'goods_title'   => $goodsInfoObj->goods_title,
                'describe'      => $goodsInfoObj->jianjie,
            ];
            $goodsInfoArr['basic_attributes'] = [
                'goods_price'            => $goodsAttributeObj->goods_price,
                'goods_unit_id'          => $goodsAttributeObj->meter_unit,
                'price_adjust_frequency' => $goodsAttributeObj->price_adjust_frequency,
                'rough_weight'           => $goodsAttributeObj->rough_weight,
                'net_weight'             => $goodsAttributeObj->net_weight,
                'meat_weight'            => $goodsAttributeObj->meat_weight,
                'inventory'              => $goodsAttributeObj->inventory,
                'minimum_order_quantity' => $goodsAttributeObj->fromsell_num,
                'specs'                  => $specArr,
                'types'                  => $typeArr,
            ];
            $goodsInfoArr['special_attributes'] = $specialAttributeArr;
            $goodsInfoArr['pictures'] = $goodsInfoObj->goodsPicture;
            $goodsInfoArr['inspection_reports'] = $goodsInfoObj->goodsInspectionReport;
            $goodsUnit = DpGoodsBasicAttribute::getGoodsUnitName($goodsAttributeObj->meter_unit);
            $goodsInfoArr['price_rules'] =
                DpPriceRule::priceRuleSplitValueUnitHandler($goodsInfoObj->priceRule, $goodsUnit);
        }

        return $goodsInfoArr;
    }

    /**
     * 获取旧的商品信息
     *
     * @param $oldGoodsId int 旧商品的ID
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function getOldGoodsInfo($oldGoodsId)
    {
        return $this->goodsRepo->getOldGoodsInfoById($oldGoodsId);
    }

    /**
     * 生成商品添加信息
     *
     * @param array $basicInfos      array 商品基本信息
     * @param       $groupNumber     int 商品组编号
     * @param       $oldGoodsInfoObj object 旧的商品信息
     * @param       $adminId         int 处理的管理员ID
     * @param       $pictureCount    int 商品图片数量
     *
     * @return array
     */
    private function genGoodsAddInfo(array $basicInfos, $groupNumber, $oldGoodsInfoObj, $adminId, $pictureCount)
    {
        // 获取商品分类的节点
        $goodsTypeNodeObj = $this->goodsTypeRepo->getGoodsTypeInfoById($basicInfos['goods_type_id']);
        $goodsTypeNode = $goodsTypeNodeObj->nodeid;
        // 获取品牌名称
        /** @var  $brandRepo BrandsRepository */
        $brandRepo = App::make(BrandsRepository::class);
        $brandObj = $brandRepo->findBrandById($basicInfos['brand_id']);
        $brandName = $brandObj->brand;
        // 商品基本信息
        $addBasicInfoArr = [
            'id'              => $basicInfos['goods_id'],
            'group_number'    => $groupNumber,
            'goods_type_id'   => $basicInfos['goods_type_id'],
            'sortid'          => $goodsTypeNode,
            'gname'           => $basicInfos['goods_name'],
            'goods_title'     => $basicInfos['goods_title'],
            'origin'          => $basicInfos['origin'],
            'brand_id'        => $basicInfos['brand_id'],
            'brand'           => $brandName,
            'goods_key'       => $oldGoodsInfoObj->goods_key,
            'boosid'          => $oldGoodsInfoObj->boosid,
            'shopid'          => $oldGoodsInfoObj->shopid,
            'shid'            => $oldGoodsInfoObj->shid,
            'adminid'         => $adminId,
            'shenghe_act'     => DpGoodsInfo::STATUS_AUDIT,
            'buygood'         => $oldGoodsInfoObj->buygood,
            'halal'           => empty($basicInfos['halal']) ? 0 : 1,
            'smuggle'         => $basicInfos['smuggle_id'],
            'picnum'          => $pictureCount,
            'integralall_num' => $oldGoodsInfoObj->integralall_num,
            //'inspection_report' => $basicInfos['inspection_report'],
            'suitableids'     => empty($oldGoodsInfoObj->suitableids) ? '' : $oldGoodsInfoObj->suitableids,
            'suitablenames'   => empty($oldGoodsInfoObj->suitablenames) ? '' : $oldGoodsInfoObj->suitablenames,
            'jianjie'         => $basicInfos['describe'],
            'audit_time'      => $oldGoodsInfoObj->audit_time,
        ];

        return $addBasicInfoArr;
    }

    /**
     * 商品基本信息更新 信息生成
     *
     * @param array $basicInfos   array   商品基本信息
     * @param       $pictureCount int 商品图片数量
     * @param       $goods_status int 商品状态 0表示不区分状态(这里主要是为商品转移用)
     *
     * @return array
     */
    private function genGoodsUpdateInfo(array $basicInfos, $pictureCount, $goods_status = 0)
    {
        // 获取商品分类的节点
        $goodsTypeNodeObj = $this->goodsTypeRepo->getGoodsTypeInfoById($basicInfos['goods_type_id']);
        $goodsTypeNode = $goodsTypeNodeObj->nodeid;
        // 获取品牌名称
        /** @var  $brandRepo BrandsRepository */
        $brandRepo = App::make(BrandsRepository::class);
        $brandObj = $brandRepo->findBrandById($basicInfos['brand_id']);
        $brandName = $brandObj->brand;
        // 商品基本信息
        $updateBasicInfoArr = [
            'goods_type_id' => $basicInfos['goods_type_id'],
            'sortid'        => $goodsTypeNode,
            'gname'         => $basicInfos['goods_name'],
            'goods_title'   => $basicInfos['goods_title'],
            'origin'        => $basicInfos['origin'],
            'brand_id'      => $basicInfos['brand_id'],
            'brand'         => $brandName,
            'halal'         => empty($basicInfos['halal']) ? 0 : 1,
            'smuggle'       => $basicInfos['smuggle_id'],
            'picnum'        => $pictureCount,
            'jianjie'       => $basicInfos['describe'],
        ];
        if ($goods_status === DpGoodsInfo::WAIT_PERFECT) {
            $updateBasicInfoArr['shenghe_act'] = DpGoodsInfo::STATUS_AUDIT;
        }

        return $updateBasicInfoArr;
    }

    /**
     * 生成基础属性的添加信息
     *
     * @param $oldGoodsAttribute object 旧的基础属性
     * @param $basicInfos        array 新的基础属性
     * @param $groupNumber       int 商品组编号
     * @param $goodsId           int 商品ID
     *
     * @return array
     */
    private function genGoodsBasicAttrInfo($oldGoodsAttribute, array $basicInfos, $groupNumber, $goodsId)
    {
        // 获取基础属性的约束类型
        /** @var  $basicAttrClass GoodsConstraintsRepository */
        $basicAttrClass = App::make(GoodsConstraintsRepository::class);
        $columnSelectArr = ['format_type_id', 'format_values'];
        // 规格兼容字符串
        $basicAttrSpecsInfoObj = $basicAttrClass
            ->getConstraintById($basicInfos['specs']['constraint_id'], $columnSelectArr);
        // 加进规格规则里的单位
        $basicValArr = json_decode($basicAttrSpecsInfoObj->format_values, true);
        foreach ($basicValArr as $key => $value) {
            if (!empty($basicInfos['specs']['values'][$key])) {
                $basicInfos['specs']['values'][$key]['unit'] = $value['unit'];
            }
        }
        $specs = $this->attrArrToText($basicInfos['specs']['values'], $basicAttrSpecsInfoObj->format_type_id);
        $basicInfos['specs']['format_type_id'] = $basicAttrSpecsInfoObj->format_type_id;
        // 型号兼容字符串
        $basicAttrTypeInfoObj = $basicAttrClass
            ->getConstraintById($basicInfos['types']['constraint_id'], $columnSelectArr);
        // 加进型号规则里的单位
        $typesValArr = json_decode($basicAttrTypeInfoObj->format_values, true);
        foreach ($typesValArr as $key => $value) {
            if (!empty($basicInfos['types']['values'][$key])) {
                $basicInfos['types']['values'][$key]['unit'] = $value['unit'];
            }
        }
        $types = $this->attrArrToText($basicInfos['types']['values'], $basicAttrTypeInfoObj->format_type_id);
        $basicInfos['types']['format_type_id'] = $basicAttrTypeInfoObj->format_type_id;
        $addBasicAttrArr = [
            'basicid'                => $oldGoodsAttribute->basicid,
            'goodsid'                => $goodsId,
            'group_number'           => $groupNumber,
            'guigei'                 => $specs,
            'specs'                  => json_encode($basicInfos['specs']),
            'xinghao'                => $types,
            'types'                  => json_encode($basicInfos['types']),
            'net_weight'             => $basicInfos['net_weight'],
            'rough_weight'           => $basicInfos['rough_weight'],
            'meat_weight'            => $basicInfos['meat_weight'],
            'meter_unit'             => $basicInfos['goods_unit_id'],
            'goods_price'            => $basicInfos['goods_price'],
            'inventory'              => $basicInfos['inventory'],
            'tag'                    => $oldGoodsAttribute->tag,
            'recommend'              => $oldGoodsAttribute->recommend,
            'recommtime'             => $oldGoodsAttribute->recommtime,
            'recomm_uid'             => empty($oldGoodsAttribute->recomm_uid) ? '' : $oldGoodsAttribute->recomm_uid,
            'opder_num'              => $oldGoodsAttribute->opder_num,
            'xiadan_num'             => $oldGoodsAttribute->xiadan_num,
            'sell_num'               => $oldGoodsAttribute->sell_num,
            'fromsell_num'           => $basicInfos['minimum_order_quantity'],
            'click_num'              => $oldGoodsAttribute->click_num,
            'previous_price'         => $oldGoodsAttribute->previous_price,
            'end_price_change_time'  => $oldGoodsAttribute->end_price_change_time,
            'price_adjust_frequency' => $basicInfos['price_adjust_frequency'],
        ];

        return $addBasicAttrArr;
    }

    /**
     * 生成商品基本属性修改信息
     *
     * @param array $basicInfos array 基本属性信息
     *
     * @return array
     */
    private function genGoodsBasicAttrUpdateInfo(array $basicInfos)
    {
        // 获取基础属性的约束类型
        /** @var  $basicAttrClass GoodsConstraintsRepository */
        $basicAttrClass = App::make(GoodsConstraintsRepository::class);
        $columnSelectArr = ['format_type_id', 'format_values'];
        // 规格兼容字符串
        $basicAttrSpecsInfoObj = $basicAttrClass
            ->getConstraintById($basicInfos['specs']['constraint_id'], $columnSelectArr);
        // 加进规格规则里的单位
        $basicValArr = json_decode($basicAttrSpecsInfoObj->format_values, true);
        foreach ($basicValArr as $key => $value) {
            if (!empty($basicInfos['specs']['values'][$key])) {
                $basicInfos['specs']['values'][$key]['unit'] = $value['unit'];
            }
        }
        $specs = $this->attrArrToText($basicInfos['specs']['values'], $basicAttrSpecsInfoObj->format_type_id);
        $basicInfos['specs']['format_type_id'] = $basicAttrSpecsInfoObj->format_type_id;
        // 型号兼容字符串
        $basicAttrTypeInfoObj = $basicAttrClass
            ->getConstraintById($basicInfos['types']['constraint_id'], $columnSelectArr);
        // 加进型号规则里的单位
        $typesValArr = json_decode($basicAttrTypeInfoObj->format_values, true);
        foreach ($typesValArr as $key => $value) {
            if (!empty($basicInfos['types']['values'][$key])) {
                $basicInfos['types']['values'][$key]['unit'] = $value['unit'];
            }
        }
        $types = $this->attrArrToText($basicInfos['types']['values'], $basicAttrTypeInfoObj->format_type_id);
        $basicInfos['types']['format_type_id'] = $basicAttrTypeInfoObj->format_type_id;
        $addBasicAttrArr = [
            'guigei'                 => $specs,
            'specs'                  => json_encode($basicInfos['specs']),
            'xinghao'                => $types,
            'types'                  => json_encode($basicInfos['types']),
            'net_weight'             => $basicInfos['net_weight'],
            'rough_weight'           => $basicInfos['rough_weight'],
            'meat_weight'            => $basicInfos['meat_weight'],
            'meter_unit'             => $basicInfos['goods_unit_id'],
            'goods_price'            => $basicInfos['goods_price'],
            'inventory'              => empty($basicInfos['inventory'])
                ? DpGoodsBasicAttribute::DEFAULT_INVENTORY
                : $basicInfos['inventory'],
            'fromsell_num'           => $basicInfos['minimum_order_quantity'],
            'price_adjust_frequency' => $basicInfos['price_adjust_frequency'],
        ];

        return $addBasicAttrArr;
    }

    /**
     * 生成商品特殊属性添加信息
     *
     * @param array $specialAttributes array 特殊属性
     * @param       $goodsId           int 商品ID
     *
     * @return array
     */
    private function genSpecialAttrInfo(array $specialAttributes, $goodsId)
    {
        $addSpecialAttrArr = [];
        /** @var  $specialAttrClass GoodsTypeSpecialAttrRepository */
        $specialAttrClass = App::make(GoodsTypeSpecialAttrRepository::class);
        $columnSelectArr = ['format_type_id', 'attribute_name', 'format_values'];
        foreach ($specialAttributes as $specialAttr) {
            $specialAttrInfoObj = $specialAttrClass
                ->getConstraintInfoById($specialAttr['constraint_id'], $columnSelectArr);
            // 加进约束规则里的单位
            $specialValArr = json_decode($specialAttrInfoObj->format_values, true);
            foreach ($specialValArr as $key => $value) {
                if (!empty($specialAttr['values'][$key]['value'])) {
                    $specialAttr['values'][$key]['unit'] = $value['unit'];
                }
            }
            $attrText = $this->attrArrToText($specialAttr['values'], $specialAttrInfoObj->format_type_id);
            $specialAttr['format_type_id'] = $specialAttrInfoObj->format_type_id;
            $addSpecialAttrArr[] = [
                'goodsid'     => $goodsId,
                'propeid'     => $specialAttr['constraint_id'],
                'prope_name'  => $specialAttrInfoObj->attribute_name,
                'prope_value' => $attrText,
                'attr_value'  => json_encode($specialAttr),
            ];
        }

        return $addSpecialAttrArr;
    }

    /**
     * 生成商品图片的添加/修改信息
     *
     * @param array $pictures array 图片信息
     * @param       $goodsId  int 商品ID
     *
     * @return array
     *                 [
     *                      'addInfo' => [
     *                          [
     *                              'goodsid', 商品ID
     *                              'ypic_path', 图片地址
     *                              'ordernum' 排列顺序
     *                          ],[...]
     *                       ],
     *                       'updateInfo' => [
     *                          [
     *                              'id' 图片记录ID
     *                              'values' => [
     *                                  'ypic_path' 图片地址
     *                                  'ordernum' 排列顺序
     *                              ]
     *                          ],[...]
     *                        ]
     *                    ]
     */
    private function genPictureInfo(array $pictures, $goodsId)
    {
        $pictureArr = [
            'addInfo'    => [],
            'updateInfo' => [],
        ];
        foreach ($pictures as $picture) {
            if ($picture['picture_id']) {
                // 修改
                $pictureArr['updateInfo'][] = [
                    'id'     => $picture['picture_id'],
                    'values' => [
                        'ypic_path' => $picture['picture_add'],
                        'ordernum'  => $picture['sort_value'],
                    ],
                ];
            } else {
                // 添加
                $pictureArr['addInfo'][] = [
                    'goodsid'   => $goodsId,
                    'ypic_path' => $picture['picture_add'],
                    'ordernum'  => $picture['sort_value'],
                ];
            }
        }

        return $pictureArr;
    }

    /**
     * 生成商品检验报告图片的添加/修改信息
     *
     * @param array $inspectionReports array 检验报告图片信息
     * @param       $goodsId           int 商品ID
     *
     * @return array
     *                 [
     *                      'addInfo' => [
     *                          [
     *                              'goods_id', 商品ID
     *                              'picture_add', 图片地址
     *                              'sort_value' 排列顺序
     *                          ],[...]
     *                       ],
     *                       'updateInfo' => [
     *                          [
     *                              'id' 图片记录ID
     *                              'values' => [
     *                                  'picture_add' 图片地址
     *                                  'sort_value' 排列顺序
     *                              ]
     *                          ],[...]
     *                        ]
     *                    ]
     */
    private function genInspectionReportInfo(array $inspectionReports, $goodsId)
    {
        $pictureArr = [
            'addInfo'    => [],
            'updateInfo' => [],
        ];
        foreach ($inspectionReports as $picture) {
            if ($picture['picture_id']) {
                // 修改
                $pictureArr['updateInfo'][] = [
                    'id'     => $picture['picture_id'],
                    'values' => [
                        'picture_add' => $picture['picture_add'],
                        'sort_value'  => $picture['sort_value'],
                    ],
                ];
            } else {
                // 添加
                $pictureArr['addInfo'][] = [
                    'goods_id'    => $goodsId,
                    'picture_add' => $picture['picture_add'],
                    'sort_value'  => $picture['sort_value'],
                ];
            }
        }

        return $pictureArr;
    }

    /**
     * 记录商品更改日志（有商品快照的记录）
     *
     * @param $eventObj object 事件对象
     */
    private function logWrite($eventObj)
    {
        Event::fire(
            'goods.operate.update.info',
            $eventObj
        );
    }
}
