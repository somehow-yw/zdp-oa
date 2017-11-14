<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/9/14
 * Time: 12:04
 */

namespace App\Repositories\Goods\Contracts;

interface GoodsRepository
{
    /**
     * 获取商品的信息
     *
     * @param int $goodsId 商品ID
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getGoodsInfoById($goodsId);

    /**
     * 更改商品的TAG
     *
     * @param int $goodsId  商品ID
     * @param int $goodsTag 商品TAG
     *
     * @return int
     */
    public function updateGoodsTag($goodsId, $goodsTag);

    /**
     * 获取旧商品的信息
     *
     * @param $goodsId int 商品ID
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getOldGoodsInfoById($goodsId);

    /**
     * 将已转移的旧商品做标记
     *
     * @param $goodsId int 商品ID
     *
     * @return void
     */
    public function updateOldGoodsTransfer($goodsId);

    /**
     * 旧商品图片（包括检验报告）的获取
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
    public function getOldGoodsPicture($goodsId, $columnSelectArr);

    /**
     * 商品基本信息添加
     *
     * @param array $addBasicInfoArr array 添加的信息 格式如:['field'=>'value']
     *
     * @return object
     */
    public function addBasicInfo(array $addBasicInfoArr);

    /**
     * 商品基本信息修改
     *
     * @param array $updateBasicInfoArr array 更改信息 格式如：['field'=>'value']
     * @param       $goodsId            int 商品ID
     *
     * @return int 更改的记录数
     */
    public function updateBasicInfo(array $updateBasicInfoArr, $goodsId);

    /**
     * 商品基础属性添加
     *
     * @param array $addBasicAttrArr array 添加的信息 格式如：['field'=>'value']
     *
     * @return object
     */
    public function addBasicAttr(array $addBasicAttrArr);

    /**
     * 商品基本属性修改
     *
     * @param array $updateBasicAttrArr array 修改信息 格式如：['field'=>'value']
     * @param       $basicId            int 基本属性ID
     *
     * @return int 更改的记录数
     */
    public function updateBasicAttr(array $updateBasicAttrArr, $basicId);

    /**
     * 商品特殊属性添加
     *
     * @param array $addSpecialAttrArr array 添加的信息 格式如：[['field'=>'value'],[...]]
     * @param       $goodsId           int 商品ID
     *
     * @return void
     */
    public function addSpecialAttr(array $addSpecialAttrArr, $goodsId = 0);

    /**
     * 商品图片的添加与修改
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
    public function addOrUpdatePicture(array $pictureInfoArr);

    /**
     * 商品检验报告图片的添加与修改
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
    public function addOrUpdateInspectionReport(array $inspectionReportInfoArr, $goodsId);

    /**
     * 根据商品ID查询商品、店铺及会员(大老板)的信息
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
    public function getGoodsBelongShopInfoByGoodsId($goodsId, array $columnSelectArr);

    /**
     * 处理并返回商品详情信息
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
     *                              ]
     *
     * @return object | null
     */
    public function getGoodsInfo($goodsId, array $columnSelectArr);

    /**
     * 取得分类下的商品数量
     *
     * @param array $goodsTypeArr array 商品分类ID 格式：[1,2,3]
     *
     * @return int
     */
    public function getGoodsNumByTypeIds(array $goodsTypeArr);

    /**
     * 更新商品价格体系
     * (包括强制删除和重建)
     *
     * @param $goodsId    integer 商品id
     * @param $priceRules array
     *
     * priceRules structure
     *            [
     *                  {
     *                    "price_rule_id":1,
     *                    "rules":[
     *                              {
     *                              "buy_num":10,
     *                              "preferential_value":200
     *                              },
     *                              {...}
     *                          ]
     *                      },
     *                      {...}
     *          ]
     *
     */
    public function updateGoodsPriceRule($goodsId, array $priceRules);
}
