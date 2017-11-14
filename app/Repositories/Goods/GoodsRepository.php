<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/9/14
 * Time: 12:04
 */

namespace App\Repositories\Goods;

use DB;

use App\Models\DpGoods;
use App\Models\DpGoodsBasicAttribute;
use App\Models\DpGoodsInfo;
use App\Models\DpGoodsSpecialAttribute;
use App\Models\DpGoodsPic;
use App\Models\DpGoodsInspectionReport;
use App\Models\DpShangHuInfo;

use App\Repositories\Goods\Contracts\GoodsRepository as RepositoriesContract;

use App\Models\DpPriceRule;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GoodsRepository.
 * 商品数据处理
 *
 * @package App\Repositories\Goods
 */
class GoodsRepository implements RepositoriesContract
{
    /**
     * 获取商品的信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::getGoodsInfoById()
     *
     * @param int $goodsId 商品ID
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getGoodsInfoById(
        $goodsId
    ) {
        $goodsInfo = DpGoodsInfo::with(
            [
                'shop' => function ($query) {
                    $query->with('market')
                        ->select('shopId', 'dianPuName', 'pianquId');
                },
            ]
        )
            ->where('id', $goodsId)
            ->where('shenghe_act', DpGoodsInfo::STATUS_NORMAL)
            ->select('shopid')
            ->first();

        return $goodsInfo;
    }

    /**
     * 更改商品的TAG
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::updateGoodsTag()
     *
     * @param int $goodsId  商品ID
     * @param int $goodsTag 商品TAG
     *
     * @return int
     */
    public function updateGoodsTag($goodsId, $goodsTag)
    {
        $updateArr = ['tag' => $goodsTag];

        return DpGoodsBasicAttribute::where('goodsid', $goodsId)
            ->update($updateArr);
    }

    /**
     * 获取旧商品的信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::getOldGoodsInfoById()
     *
     * @param int $goodsId 商品ID
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getOldGoodsInfoById($goodsId)
    {
        $goodsInfo = DpGoods::with(
            [
                'goodsAttribute' => function ($query) {
                    $query->select(
                        [
                            'basicid',
                            'goodsid',
                            'tag',
                            'recommend',
                            'recommtime',
                            'recomm_uid',
                            'opder_num',
                            'xiadan_num',
                            'sell_num',
                            'click_num',
                            'previous_price',
                            'end_price_change_time',
                        ]
                    );
                },
                'shop'           => function ($query) {
                    $query->select(
                        [
                            'shopId',
                            'pianquId',
                        ]
                    );
                },
            ]
        )->where('id', $goodsId)
            ->select(
                [
                    'id',
                    'goods_key',
                    'boosid',
                    'shopid',
                    'shid',
                    'addtime',
                    'buygood',
                    'integralall_num',
                    'suitableids',
                    'suitablenames',
                    'audit_time',
                ]
            )
            ->first();

        return $goodsInfo;
    }

    /**
     * 将已转移的旧商品做标记
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::updateOldGoodsTransfer()
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function updateOldGoodsTransfer($goodsId)
    {
        DpGoods::where('id', $goodsId)
            ->update(['transfer' => DpGoods::TRANSFER]);
    }

    /**
     * 旧商品图片（包括检验报告）的获取
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::getOldGoodsPicture()
     *
     * @param       $goodsId         int 商品ID
     * @param array $columnSelectArr array 获取的字段
     *
     *                               [
     *                                  'goods'=>[], 商品
     *                                  'goodsPicture'=>[], 商品图片
     *                               ]
     *
     * @return object|null
     */
    public function getOldGoodsPicture($goodsId, $columnSelectArr)
    {
        $goodsFieldArr = array_merge($columnSelectArr['goods'], ['id']);
        $goodsPictureFieldArr = array_merge($columnSelectArr['goodsPicture'], ['goodsid']);
        $goodsPictureInfo = DpGoods::with(
            [
                'goodsPicture' => function ($query) use ($goodsPictureFieldArr) {
                    $query->select($goodsPictureFieldArr);
                },
            ]
        )->where('id', $goodsId)
            ->select($goodsFieldArr)
            ->first();

        return $goodsPictureInfo;
    }

    /**
     * 商品基本信息添加
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::addBasicInfo()
     *
     * @param array $addBasicInfoArr array 添加的信息 格式如:['key'=>'value']
     *
     * @return object
     */
    public function addBasicInfo(array $addBasicInfoArr)
    {
        return DpGoodsInfo::create($addBasicInfoArr);
    }

    /**
     * 商品基本信息修改
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::updateBasicInfo()
     *
     * @param array $updateBasicInfoArr array 更改信息
     * @param       $goodsId            int 商品ID
     *
     * @return int
     */
    public function updateBasicInfo(array $updateBasicInfoArr, $goodsId)
    {
        return DpGoodsInfo::where('id', $goodsId)
            ->update($updateBasicInfoArr);
    }

    /**
     * 商品基本属性修改
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::updateBasicAttr()
     *
     * @param array $updateBasicAttrArr array 修改信息 格式如：['field'=>'value']
     * @param       $basicId            int 基本属性ID
     *
     * @return int 更改的记录数
     */
    public function updateBasicAttr(array $updateBasicAttrArr, $basicId)
    {
        DpGoodsBasicAttribute::where('basicid', $basicId)
            ->update($updateBasicAttrArr);
    }

    /**
     * 商品基础属性添加
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::addBasicAttr()
     *
     * @param array $addBasicAttrArr array 添加的信息 格式如：['field'=>'value']
     *
     * @return object
     */
    public function addBasicAttr(array $addBasicAttrArr)
    {
        return DpGoodsBasicAttribute::create($addBasicAttrArr);
    }

    /**
     * 商品特殊属性添加
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::addSpecialAttr()
     *
     * @param array $addSpecialAttrArr array 添加的信息 格式如：[['field'=>'value'],[...]]
     * @param       $goodsId           int 商品ID
     *
     * @return void
     */
    public function addSpecialAttr(array $addSpecialAttrArr, $goodsId = 0)
    {
        // 需先删除此商品的所有旧属性，再添加新的属性（先删除是由于目前没有针对的ID传入）
        DpGoodsSpecialAttribute::where('goodsid', $goodsId)
            ->delete();
        DpGoodsSpecialAttribute::insert($addSpecialAttrArr);
    }

    /**
     * 商品图片的添加与修改
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::addOrUpdatePicture()
     *
     * @param array $pictureInfoArr array 商品图片信息
     *
     *                              [
     *                                  'addInfo' => [
     *                                      [
     *                                          'goodsid', 商品ID
     *                                          'ypic_path', 图片地址
     *                                          'ordernum' 排列顺序
     *                                      ],[...]
     *                                  ],
     *                                  'updateInfo' => [
     *                                      [
     *                                          'id' 图片记录ID
     *                                          'values' => [
     *                                              'ypic_path' 图片地址
     *                                              'ordernum' 排列顺序
     *                                          ]
     *                                      ],[...]
     *                                  ]
     *                              ]
     *
     * @return void
     */
    public function addOrUpdatePicture(array $pictureInfoArr)
    {
        if (count($pictureInfoArr['updateInfo'])) {
            foreach ($pictureInfoArr['updateInfo'] as $updateInfo) {
                DpGoodsPic::where('picid', $updateInfo['id'])
                    ->update($updateInfo['values']);
            }
        }
        DpGoodsPic::insert($pictureInfoArr['addInfo']);
    }

    /**
     * 商品检验报告图片的添加与修改
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::addOrUpdateInspectionReport()
     *
     * @param array $inspectionReportInfoArr array 图片信息
     *
     *                  [
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
     * @param       $goodsId                 int 商品ID
     *
     * @return void
     */
    public function addOrUpdateInspectionReport(array $inspectionReportInfoArr, $goodsId)
    {
        $picture = '';  // 主要是兼容之前的程序，需要写一张图片进商品信息表
        if (count($inspectionReportInfoArr['updateInfo'])) {
            foreach ($inspectionReportInfoArr['updateInfo'] as $key => $updateInfo) {
                DpGoodsInspectionReport::where('id', $updateInfo['id'])
                    ->update($updateInfo['values']);
                if (0 === $key) {
                    $picture = $updateInfo['values']['picture_add'];
                }
            }
        }
        DpGoodsInspectionReport::insert($inspectionReportInfoArr['addInfo']);
        if (empty($picture)) {
            $picture = $inspectionReportInfoArr['addInfo'][0]['picture_add'];
        }
        // 修改商品表的检验报告兼容图片
        DpGoodsInfo::where('id', $goodsId)
            ->update(['inspection_report' => $picture]);
    }

    /**
     * 根据商品ID查询商品、店铺及会员(大老板)的信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::getGoodsBelongShopInfoByGoodsId()
     *
     * @param       $goodsId         int 商品ID
     * @param array $columnSelectArr array 所需字段
     *
     *                               [
     *                                  'goods'=>[],
     *                                  'shop'=>[],
     *                                  'user'=>[],
     *                               ]
     *
     * @return object
     */
    public function getGoodsBelongShopInfoByGoodsId($goodsId, array $columnSelectArr)
    {
        $selectGoodsArr = array_merge($columnSelectArr['goods'], ['id', 'shopid']);
        $selectShopArr = array_merge($columnSelectArr['shop'], ['shopId']);
        $selectUserArr = array_merge($columnSelectArr['user'], ['shopId']);
        $goodsInfo = DpGoodsInfo::with(
            [
                'shop' => function ($query) use ($selectShopArr, $selectUserArr) {
                    $query->with(
                        [
                            'user' => function ($query) use ($selectUserArr) {
                                $query->where('laoBanHao', DpShangHuInfo::SHOP_BOOS)
                                    ->select($selectUserArr);
                            },
                        ]
                    )->select($selectShopArr);
                },
            ]
        )->where('id', $goodsId)
            ->select($selectGoodsArr)
            ->first();

        return $goodsInfo;
    }

    /**
     * 处理并返回商品详情信息
     *
     * @see \App\Repositories\Goods\Contracts\GoodsRepository::getGoodsInfo()
     *
     * @param       $goodsId         int 商品ID
     * @param array $columnSelectArr array 所需字段
     *
     *                               [
     *                                  'goods'                 => ['',''], 商品信息
     *                                  'goodsAttribute'        => ['',''], 商品基本属性
     *                                  'specialAttribute'      => ['',''], 商品特殊属性
     *                                  'goodsPicture'          => ['',''], 商品图片
     *                                  'goodsInspectionReport' => ['',''], 商品检验报告图片
     *                                  'goodsPriceRule'        => ['',''], 商品价格规则
     *                              ]
     *
     * @return object | null
     */
    public function getGoodsInfo($goodsId, array $columnSelectArr)
    {
        $goodsSelectArr = array_merge($columnSelectArr['goods'], ['id']);
        $goodsAttrSelectArr = array_merge($columnSelectArr['goodsAttribute'], ['goodsid']);
        $specialAttrSelectArr = array_merge($columnSelectArr['specialAttribute'], ['goodsid']);
        $goodsPicSelectArr = array_merge($columnSelectArr['goodsPicture'], ['goodsid']);
        $inspectionReportSelectArr = array_merge($columnSelectArr['goodsInspectionReport'], ['goods_id']);
        $goodsPriceRuleSelectArr = array_merge($columnSelectArr['goodsPriceRule'], ['goods_id']);

        $goodsInfoObj = DpGoodsInfo::with(
            [
                'goodsAttribute'        => function ($query) use ($goodsAttrSelectArr) {
                    $query->select($goodsAttrSelectArr);
                },
                'specialAttribute'      => function ($query) use ($specialAttrSelectArr) {
                    $query->select($specialAttrSelectArr);
                },
                'goodsPicture'          => function ($query) use ($goodsPicSelectArr) {
                    $query->select($goodsPicSelectArr)
                        ->orderBy('ordernum', 'asc');
                },
                'goodsInspectionReport' => function ($query) use ($inspectionReportSelectArr) {
                    $query->select($inspectionReportSelectArr)
                        ->orderBy('sort_value', 'asc');
                },
                'priceRule'             => function ($query) use ($goodsPriceRuleSelectArr) {
                    $query->select($goodsPriceRuleSelectArr);
                },
            ]
        )->where('id', $goodsId)
            ->select($goodsSelectArr)
            ->first();

        return $goodsInfoObj;
    }

    /**
     * 取得分类下的商品数量
     *
     * @param array $goodsTypeArr array 商品分类ID 格式：[1,2,3]
     *
     * @return int
     */
    public function getGoodsNumByTypeIds(array $goodsTypeArr)
    {
        return DpGoodsInfo::whereIn('goods_type_id', $goodsTypeArr)
            ->count();
    }

    /**
     * @inheritDoc
     * priceRules structure
     * [
     *  {
     *      "price_rule_id":1,
     *      "rules":[
     *           {
     *           "buy_num":10,
     *           "preferential":200
     *           },{...}
     *       ]
     *  },
     *  {...}
     * ]
     *
     */
    public function updateGoodsPriceRule($goodsId, array $priceRules)
    {
        //强制删除该商品id所关联的所有价格体系
        DpPriceRule::where('goods_id', $goodsId)
            ->forceDelete();
        $priceRulesColl = new Collection($priceRules);
        $priceRulesColl->each(function ($item, $key) use ($goodsId) {
            $priceRuleId = $item['price_rule_id'];
            $rulesColl = new Collection($item['rules']);
            $rulesColl = $rulesColl->map(function ($rule, $key) use ($goodsId, $priceRuleId) {
                $rule['preferential'] =
                    DpPriceRule::convertPreferentialValueForSave($priceRuleId, $rule['preferential_value']);
                $rule['goods_id'] = $goodsId;
                $rule['price_rule_id'] = $priceRuleId;
                $rule['created_at'] = time_now();
                $rule['updated_at'] = time_now();

                // 只要数据库需要的字段
                $rule = array_only(
                    $rule,
                    [
                        'buy_num',
                        'preferential',
                        'goods_id',
                        'price_rule_id',
                        'created_at',
                        'updated_at',
                    ]
                );

                return $rule;
            });
            DpPriceRule::insert($rulesColl->toArray());
        });
    }
}
