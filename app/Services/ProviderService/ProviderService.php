<?php

namespace App\Services\ProviderService;

use App\Exceptions\AppException;
use App\Models\DpShangHuInfo;
use App\Models\ServiceHandleLog;
use App\Utils\ExcelWriterUtil;
use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\ServiceProvider\Data\Models\Area as SpAreaModel;
use Zdp\ServiceProvider\Data\Models\Area;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderGoods;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;
use Zdp\ServiceProvider\Data\Models\ShopType;
use Zdp\ServiceProvider\Data\Models\WechatAccount;

class ProviderService
{
    // 订单导出的送货时间界限
    protected $deliveryTime = "07:00:00";
    const CACHE_PREFIX = 'sp_city_name_';
    // 导出订单的送货区域编号session时长
    const CACHE_TIME = 720;

    protected $serviceProvider = [
        'shop_name',            // 店铺名称
        'user_name',            // 用户名
        'mobile',               // 手机号
        'address',              // 地址
        'market_ids',           // 服务市场ID串 半角逗号分隔
    ];
    protected $wechatAccount   = [
        'wechat_name',      // 公众号名称
        'appid',            // 服务商公众号应用ID AppID
        'secret',           // 服务商公众号应用密钥 AppSecret
        'token',            // 服务商公众号令牌 Token
        'aes_key',          // 服务商公众号消息加解密密钥 EncodingAESKey
    ];

    /**
     * 获取所有服务商列表
     *
     * @param      $page
     * @param      $size
     * @param null $status
     *
     * @return array
     */
    public function index($page, $size, $status = null)
    {
        $query = ServiceProvider
            ::query()
            ->with([
                'areas' => function ($query) {
                    $query->with(['province', 'city', 'county']);
                },
            ]);

        if (empty($status)) {
            $query->orderBy('status', 'asc');
        } else {
            $query->where('status', $status);
        }

        $allApplies = $query->paginate($size, ['*'], null, $page);

        $applies = array_map(function ($a) {
            return ServiceProvider::formatForAdmin(
                $a,
                ServiceProvider::FORMAT_WECHAT
            );
        }, $allApplies->items());

        return [
            'list'      => $applies,
            'total'     => $allApplies->total(),
            'current'   => $allApplies->currentPage(),
            'last_page' => $allApplies->lastPage(),
        ];
    }

    /**
     * 查看服务商信息
     *
     * @param $id
     *
     * @return array
     */
    public function show($id)
    {
        $shop = ServiceProvider::where('zdp_user_id', $id)
                               ->get();

        $ret = array_map(function ($a) {
            return ServiceProvider::formatForAdmin(
                $a,
                ServiceProvider::FORMAT_WECHAT
            );
        }, $shop->all());

        return $ret;
    }

    /**
     * 搜索服务商
     *
     * @param $searchType 1:店铺名搜索 2:手机号搜索
     * @param $content
     *
     * @return array
     */
    public function search($searchType, $content)
    {
        $query = ServiceProvider::query();

        switch ($searchType) {
            case 1:
                $query->where('shop_name', 'like', '%' . $content . '%');
                break;
            case 2:
                $query->where('mobile', $content);
                break;
        }

        $getInfo = $query->get();

        $services = array_map(function ($a) {
            return ServiceProvider::formatForAdmin(
                $a,
                ServiceProvider::FORMAT_WECHAT
            );
        }, $getInfo->all());

        return $services;
    }

    /**
     * 电话模糊搜索提示
     *
     * @param $mobile
     *
     * @return Collection
     */
    public function hint($mobile = null)
    {
        $query = ServiceProvider::query();

        if (!empty($mobile)) {
            $query->where('mobile', 'like', '%' . $mobile . '%');
        }

        return $query->get()
                     ->map(function ($a) {
                         return ServiceProvider::formatForAdmin(
                             $a,
                             ServiceProvider::FORMAT_HINT
                         );
                     });
    }

    /**
     * 服务商客户分类列表
     *
     * @return array
     */
    public function sort()
    {
        $sorts = array_map(function ($a) {
            return ShopType::formatForAdmin($a);
        }, ShopType::get()->all());

        return $sorts;
    }

    /**
     * 添加服务商客户分类
     *
     * @param $name
     */
    public function sortAdd($name)
    {
        ShopType::create(['type_name' => $name]);
    }

    /**
     * 服务商删除
     *
     * @param $userId integer OA操作员ID
     * @param $spUid  integer 注册服务商的会员ID
     */
    public function del($userId, $spUid)
    {
        \DB::connection('mysql_service_provider')->transaction(function () use (
            $userId,
            $spUid
        ) {
            ServiceProvider::query()->where('zdp_user_id', $spUid)->delete();

            ServiceHandleLog::create([
                'sp_id'   => $spUid,
                'uid'     => $userId,
                'operate' => ServiceHandleLog::SERVICE_DEL,
            ]);
        });
    }

    /**
     * 服务商确认/关闭
     *
     * @param $userId
     * @param $handle
     * @param $spIds
     *
     * @return mixed
     * @throws AppException
     */
    public function handle($userId, $handle, $spIds)
    {
        $status = self::getStatus($spIds);

        if (count($status) != 1) {
            throw new AppException('包含不一致状态');
        }

        if ($handle == $status->get(0)['status']) {
            throw new AppException('修改状态和当前状态一致');
        }

        // 审核通过时，判断信息是否完整
        if ($handle == ServiceProvider::PASS) {
            self::judgeInfo($spIds);
        }

        switch ($handle) {
            case ServiceProvider::PASS:
                $operate = ServiceHandleLog::SERVICE_CONFIRM;
                break;
            case ServiceProvider::DENY:
                $operate = ServiceHandleLog::SERVICE_CLOSE;
                break;
            default:
                throw new AppException('状态错误');
        }

        foreach ($spIds as $spId) {
            \DB::connection('mysql_service_provider')->transaction(function (
            ) use (
                $userId,
                $handle,
                $operate,
                $spId
            ) {
                /** @var ServiceProvider $serviceProvider */
                $serviceProvider =
                    ServiceProvider::where('zdp_user_id', $spId)->first();
                $serviceProvider->status = $handle;
                $serviceProvider->save();

                if (ServiceProvider::PASS == $handle) {
                    /** @var WechatAccount $wechatAccount */
                    $wechatAccount = $serviceProvider->wechatAccount;
                    $subDomain = $wechatAccount->source;

                    $this->sendMsgToShopOwner($spId, $subDomain);
                }

                ServiceHandleLog::create([
                    'sp_id'   => $spId,
                    'uid'     => $userId,
                    'operate' => $operate,
                ]);
            });
        }
    }

    protected function judgeInfo($spIds)
    {
        $spQuery = ServiceProvider::query();
        $waQuery = WechatAccount::query();
        foreach ($spIds as $spId) {
            $spQuery = clone $spQuery;
            $waQuery = clone $waQuery;

            $spInfo = $spQuery->where('zdp_user_id', $spId)
                              ->first();

            $waInfo = $waQuery->where('sp_id', $spId)->first();

            foreach ($this->serviceProvider as $column) {
                if (empty($spInfo->$column)) {
                    throw new AppException(sprintf('服务商基础信息 %s 未完善', $column));
                }
            }

            foreach ($this->wechatAccount as $column) {
                if (empty($waInfo->$column)) {
                    throw new AppException(sprintf('服务商公众号信息 %s 未完善', $column));
                }
            }
        }
    }

    /**
     * 使用hashids生成2级域名
     *
     * @param $spid
     *
     * @return string
     */
    protected function generateSubDomain($spid)
    {
        return \Hashids::encode($spid);
    }

    protected function getStatus($spIds)
    {
        $status = ServiceProvider::whereIn('zdp_user_id', $spIds)
                                 ->select('status')
                                 ->distinct()
                                 ->get();

        return $status;
    }

    /**
     * 获取服务商操作日志
     *
     * @param $page
     * @param $size
     *
     * @return mixed
     */
    public function log($page, $size)
    {
        # todo 后台管理员信息(合伙人项目时添加)
        $logs = ServiceHandleLog
            ::join('users', 'service_handle_log.uid', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->orderBy('service_handle_log.id', 'desc')
            ->select([
                'service_handle_log.sp_id',
                'service_handle_log.operate',
                'service_handle_log.created_at',
                'users.user_name',
                'departments.department_name',
            ])
            ->paginate($size, ['*'], null, $page);

        return $logs;
    }

    /**
     * @param $spid
     *
     * @param $subDomain
     *
     * @throws AppException
     */
    protected function sendMsgToShopOwner($spid, $subDomain)
    {
        $user = DpShangHuInfo::find($spid);
        if (empty($user)) {
            throw new AppException(sprintf("用户:%s不存在", $spid));
        }

        $openId = $user->OpenID;

        $requestDataArr = [
            'content'  => '您已经通过找冻品网服务商审核，请点击该链接激活你的账户',
            'link_url' => sprintf(env('SERVICE_PROVIDER_DOMAIN') . '/single',
                $subDomain),
            'open_id'  => $openId,
            'first'    => '请激活你的服务商账户',
        ];

        $signKey = config('signature.wechat_sign_key');
        $signedRequestDataArr =
            RequestDataEncapsulationUtil::requestDataSign($requestDataArr,
                $signKey);

        $url = sprintf("%s?m=SendInform&c=WeChatInform&a=sendSpAuditNotice",
            config('request_url.wechat_request_url'));

        /** @var HTTPRequestUtil $requestUtil */
        $requestUtil = app(HTTPRequestUtil::class);
        $response = $requestUtil->post($url, $signedRequestDataArr);
        $response = @json_decode($response, true);

        if (empty($response) || !isset($response['code']) ||
            $response['code'] != 0
        ) {
            throw new AppException(
                sprintf("发送激活链接失败 用户id:%s 返回:%s", $spid,
                    array_get($response, 'message', "未知"))
            );
        }
    }

    /**
     * 获取所有省份信息
     *
     * @return array
     */
    public function getProvince()
    {
        $provinces = SpAreaModel::where('level', SpAreaModel::LEVEL_PROVINCE)
                                ->get();

        $data = array_map(function ($key) {
            return self::formatForAdmin($key);
        }, $provinces->all());

        return $data;
    }

    /**
     * 获取子区域信息
     *
     * @param $id
     *
     * @return array
     */
    public function getChildren($id)
    {
        $cities = SpAreaModel::where('pid', $id)
                             ->get();

        $data = array_map(function ($key) {
            return self::formatForAdmin($key);
        }, $cities->all());

        return $data;
    }

    /**
     * 格式化后端需要的区域数据
     *
     * @param SpAreaModel $area
     *
     * @return array
     */
    protected function formatForAdmin(SpAreaModel $area)
    {
        return [
            'id'    => $area->id,
            'name'  => $area->name,
            'level' => $area->level,
        ];
    }

    /**
     * 服务商信息更改
     *
     * @param $userId
     * @param $updateArr
     *
     * @return int
     * @throws \App\Exceptions\AppException
     */
    public function updateInfo($userId, $updateArr)
    {
        // 可更改字段
        $editFieldArr = [
            'province_id' => '',
            'city_id'     => '',
            'county_id'   => '',
        ];
        $newArr = array_diff_key($updateArr, $editFieldArr);
        if (!empty($newArr)) {
            foreach ($newArr as $key => $value) {
                unset($updateArr[$key]);
            }
        }
        if (empty($updateArr)) {
            throw new AppException('更改信息不正确');
        }
        $spInfo = ServiceProvider::query()
                                 ->where('zdp_user_id', $userId)
                                 ->select('address')
                                 ->first();
        if (is_null($spInfo)) {
            throw new AppException('服务商不存在');
        }

        $addressArr = explode('-', $spInfo->address);
        // 如果地址中包含了原有的行政区，又进行了行政区的修改，则去掉
        if (count($addressArr) > 1
            && !empty($updateArr['province_id'])
            && !empty($updateArr['city_id'])
        ) {
            $updateArr['address'] = $addressArr[1];
        }
        $updateNum = ServiceProvider::query()
                                    ->where('zdp_user_id', $userId)
                                    ->update($updateArr);

        return $updateNum;
    }

    /**
     * 订单导出
     *
     * @param string  $start 导出开始时间
     * @param string  $end   导出结束时间
     * @param integer $spId  服务商id
     *
     * @return string
     */
    public function export($start, $end, $spId = null, $status = [])
    {
        $query = Order::query();
        $query->leftJoin('users as u', 'u.id', '=', 'orders.user_id');
        // 判断是否有服务商id传入
        if (!empty($spId)) {
            $query->where('orders.sp_id', $spId);
        }

        if (!empty($status)) {
            $query->whereIn('orders.status', (array)$status);
        } else {
            $query->whereIn(
                'orders.status',
                [
                    Order::NEW_ORDER,
                    Order::UNDELIVERED,
                    Order::DELIVERING,
                    Order::RECEIVED,
                    Order::CANCELED,
                    Order::PAY_SUCCESS,
                ]
            );
        }

        $query->where('orders.created_at', '>=', $start)
              ->where('orders.created_at', '<=',
                  Carbon::createFromFormat('Y-m-d', $end)->endOfDay())
              ->select([
                  'orders.id', // 订单id
                  'orders.order_no', // 订单号 1
                  'orders.created_at',// 下单时间 4
                  'orders.consignee_info', // 收货信息  收货电话/地址 3 6
                  'orders.payment',    // 付款方式 7
                  'orders.buy_count',    // 购买商品总数 8
                  'orders.order_amount',    // 订单金额 9
                  'orders.status', // 订单状态

                  'u.id as user_id', // 用户id
                  'u.shop_name', // 店铺名字 2
                  'u.mobile_phone', // 买家电话 22
                  'u.province_id', // 获取省id，以便生成送货区域编号
                  'u.city_id', // 获取市id，以便生成送货区域编号
                  'u.county_id', // 获取区县id，以便生成送货区域编号
              ]);

        $data = $query->get();

        $data = $data->sortBy(function ($val) {
            return Order::handleDeliveryInfo($val->consignee_info)['buyer_address'];
        });

        // 处理配送时间 和进货时间
        // 根据新需求，送货时间改成当前导出订单的时间
        $deliveryTime = Carbon::now()->toDateString();
        $exInfo = [];
        foreach ($data as $info) {
            // 处理商品信息
            $goodsInfos = OrderGoods::where('order_id', $info->id)
                                    ->get();
            $payment = Order::$paymentNameArr[$info->payment];
            $goodsDetail = [];

            $deliveryInfo = Order::handleDeliveryInfo($info->consignee_info);

            foreach ($goodsInfos as $good) {
                // 处理商品信息
                $goodsInfo = OrderGoods::handleGoodsInfo($good->goods_info);
                // 处理商户信息
                $sellerInfo = DpGoodsInfo::with('shop', 'shop.market', 'type',
                    'goodsAttribute')
                                         ->where('id', $goodsInfo['goods_id'])
                                         ->first();
                $goodsPrice = $goodsInfo['goods_price_add'];
                $goodsNum = $goodsInfo['goods_num'];
                $goodsDetail[] = [
                    'goods_sort'        => $sellerInfo->type->sort_name,
                    'goods_id'          => $good->goods_id,
                    // 新增
                    'purchase_date'     => $deliveryTime,
                    // 商品进货时间
                    'purchase_price'    => $sellerInfo->goodsAttribute->goods_price,
                    'goods_name'        => $goodsInfo['goods_name'] . ' ' .
                                           $goodsInfo['guigei'] . ' ' .
                                           $goodsInfo['xinghao'],
                    'goods_price'       => $goodsPrice,
                    'goods_num'         => $goodsNum,
                    'goods_total_price' => $goodsPrice * $goodsNum,
                    'payment'           => $payment,
                    'seller_shop'       => $sellerInfo->shop->dianPuName,
                    'pianqu'            => $sellerInfo->shop->market->pianqu .
                                           ' ' . $sellerInfo->shop->xiangXiDiZi,
                    'seller_mobile'     => $sellerInfo->shop->jieDanTel,
                    'user_mobile'       => $deliveryInfo['buyer_mobile'],
                    'goods_unit'        => $goodsInfo['goods_unit'],
                ];
            }

            $exInfo[] = [
                'user_id'          => $info->user_id,
                'order_no'         => $info->order_no,
                'user_shop'        => $deliveryInfo['buyer_name'],
                'delivery_mobile'  => $deliveryInfo['buyer_mobile'],
                'add_time'         => $info->created_at->toDateTimeString(),
                'delivery_time'    => $deliveryTime,
                'delivery_address' => $deliveryInfo['buyer_address'],
                'payment'          => $payment,
                'total_num'        => $info->buy_count,
                'order_price'      => $info->order_amount,
                'delivery_amount'  => '0',
                'total_price'      => $info->order_amount,
                'goods_detail'     => $goodsDetail,
                'province_id'      => $info->province_id,
                'city_id'          => $info->city_id,
                'county_id'        => $info->county_id,
                'status'           => Order::formatStatus($info->status),
            ];
        }
        // 处理合并项目
        $printArr = self::combineArr($exInfo);

        $data = $this->handleExcel($printArr);

        return ExcelWriterUtil::excelDownload('服务商订单' . $start . '到' . $end,
            $data);
    }

    // 合并相同项目
    protected function combineArr($arr)
    {
        $examArr = $arr;
        // 查找不需要合并的项
        $notCombine = [];
        foreach ($arr as $key => $item) {
            if (in_array($key, array_dot($notCombine))) {
                continue;
            }
            // 查询相同条数
            foreach ($examArr as $k => $v) {
                if (
                    $item['status'] == $v['status'] &&
                    $item['delivery_mobile'] == $v['delivery_mobile'] &&
                    $item['delivery_address'] == $v['delivery_address'] &&
                    Carbon::parse($item['add_time'])->toDateString()
                    === Carbon::parse($v['add_time'])->toDateString() &&
                    $item['payment'] == $v['payment'] && $key != $k
                ) {
                    $notCombine[$key][$k] = $k;
                }
            }
        }
        // 合并记录
        foreach ($notCombine as $key => $item) {
            foreach ($item as $k => $v) {
                $arr[$key]['total_num'] =
                    $arr[$key]['total_num'] + $arr[$k]['total_num'];
                $arr[$key]['order_price'] =
                    $arr[$key]['order_price'] + $arr[$k]['order_price'];
                $arr[$key]['delivery_amount'] =
                    $arr[$key]['delivery_amount'] + $arr[$k]['delivery_amount'];
                $arr[$key]['total_price'] =
                    $arr[$key]['total_price'] + $arr[$k]['total_price'];
                $arr[$key]['goods_detail'] =
                    array_merge($arr[$key]['goods_detail'],
                        $arr[$k]['goods_detail']);

                unset($arr[$k]);
            }
        }

        return $arr;
    }

    // 导出表格数组组装
    protected function handleExcel($data)
    {
        $mergeColumnArr = [
            'columns' => [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'AD',
            ],
            'rows'    => [[1, 2]],  // 格式 [[1,2], [3,5], [7,8], ...]
        ];
        $mergeCellsArr = ['M1:AB1'];     // 格式 ['A1:A5', 'B1:C1', ...]
        $columnTypeArr = [
            'D'  => \PHPExcel_Style_NumberFormat::FORMAT_TEXT,
            'E'  => 'yyyy/mm/dd;@',
            'F'  => 'yyyy/mm/dd',
            'W'  => \PHPExcel_Style_NumberFormat::FORMAT_TEXT,
            'X'  => 'yyyy/mm/dd',
            'AB' => \PHPExcel_Style_NumberFormat::FORMAT_TEXT,
        ];
        $downloadDataArr['data'] = [
            0 => [
                '订单号',
                '订单状态',
                '店铺名称',
                '客户电话',
                '下单时间',
                '送货时间',
                '地址',
                '付款方式',
                '购买总数',
                '商品金额',
                '运费',
                '总计金额',
                '商品明细',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '送货区域',
            ],
            1 => [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '品类',
                '商品id',
                '品名',
                '单价',
                '数量',
                '单位',
                '合计',
                '备注',
                '卖家',
                '地址',
                '电话',
                '进货日期',
                '进货价格',
                '进货数量',
                '买家店铺',
                '买家电话',
                '送货区域',
                '送货区域',
            ],
        ];
        $rowsNum = 2;   // 下一数据的起始行
        $driverNumArr = []; // 地区用户编号
        foreach ($data as $value) {
            $address = $value['delivery_address'];

            $matches = [];
            mb_ereg('([^区市县]+[区|市|县])', mb_substr($address, 6), $matches);

            $driverNum = array_get($matches, 0, '');

            if (empty($driverNumArr[$driverNum])) {
                $driverNumArr[$driverNum] = [
                    'countNum'                => 1,
                    $value['delivery_mobile'] => 1,
                ];
            } elseif (empty($driverNumArr[$driverNum][$value['delivery_mobile']])) {
                $driverNumArr[$driverNum][$value['delivery_mobile']] =
                    ++$driverNumArr[$driverNum]['countNum'];
            }
            $driverNo =
                $driverNum . '_' .
                $driverNumArr[$driverNum][$value['delivery_mobile']];
            // 拼凑数据
            $downloadDataArr['data'][$rowsNum] = [
                $value['order_no'],
                $value['status'],
                $value['user_shop'],
                $value['delivery_mobile'],
                $value['add_time'],
                $value['delivery_time'],
                $value['delivery_address'],
                $value['payment'],
                $value['total_num'],
                $value['order_price'],
                $value['delivery_amount'],
                $value['total_price'],
                $value['goods_detail'][0]['goods_sort'],
                $value['goods_detail'][0]['goods_id'],
                $value['goods_detail'][0]['goods_name'],
                $value['goods_detail'][0]['goods_price'],
                $value['goods_detail'][0]['goods_num'],
                $value['goods_detail'][0]['goods_unit'],
                $value['goods_detail'][0]['goods_total_price'],
                $value['goods_detail'][0]['payment'],
                $value['goods_detail'][0]['seller_shop'],
                $value['goods_detail'][0]['pianqu'],
                $value['goods_detail'][0]['seller_mobile'],
                $value['goods_detail'][0]['purchase_date'],
                $value['goods_detail'][0]['purchase_price'],
                $value['goods_detail'][0]['goods_num'],
                $value['user_shop'],
                $value['goods_detail'][0]['user_mobile'],
                $driverNo,
                $driverNo,
            ];
            $rowsNum++;
            $goodsDetial = $value['goods_detail'];
            if (count($goodsDetial) > 1) {
                $elseArr = array_except($goodsDetial, 0);
                // 合并的起始行
                $startRaw = $rowsNum;
                foreach ($elseArr as $goods) {
                    $downloadDataArr['data'][$rowsNum] = [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        $goods['goods_sort'],
                        $goods['goods_id'],
                        $goods['goods_name'],
                        $goods['goods_price'],
                        $goods['goods_num'],
                        $goods['goods_unit'],
                        $goods['goods_total_price'],
                        $goods['payment'],
                        $goods['seller_shop'],
                        $goods['pianqu'],
                        $goods['seller_mobile'],
                        $goods['purchase_date'],
                        $goods['purchase_price'],
                        $goods['goods_num'],
                        $value['user_shop'],
                        $goods['user_mobile'],
                        $driverNo,
                        '',
                    ];
                    $rowsNum++;
                }
                $mergeColumnArr['rows'][] = [$startRaw, $rowsNum];
            }
        }
        $downloadDataArr['format'] = [
            'columnMerge' => $mergeColumnArr,
            'cellsMerge'  => $mergeCellsArr,
            'columnType'  => $columnTypeArr,
        ];

        return $downloadDataArr;
    }

    // 根据省市区县返回编号
    private function getAreaPre($cityId)
    {
        $cacheArrNo = self::CACHE_PREFIX . $cityId;
        if (!empty($cacheNo)) {
            return $cacheArrNo;
        }
        $areaInfos = Area::where('pid', $cityId)
                         ->where('level', Area::LEVEL_DISTRICT)
                         ->select(['id', 'name'])
                         ->orderBy('id', 'asc')
                         ->get();
        $countyPre = [];
        foreach ($areaInfos as $areaInfo) {
            $countyPre[$areaInfo->id] = $areaInfo->name;
        }
        \Cache::put($cacheArrNo, $countyPre, self::CACHE_TIME);

        return $countyPre;
    }
}
