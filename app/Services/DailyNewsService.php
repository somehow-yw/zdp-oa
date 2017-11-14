<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/8/27
 * Time: 17:50
 */

namespace App\Services;

use DB;
use App;
use Carbon\Carbon;
use Event;

use App\Repositories\DailyNews\Contracts\MessageSendUsersRepository;
use App\Repositories\DailyNews\Contracts\DailyNewsInfoRepository;
use App\Repositories\DailyNews\Contracts\DailyNewsLogRepository;
use App\Repositories\DailyNews\Contracts\DailyNewsGodsRepository;
use App\Repositories\Goods\Contracts\GoodsPriceChangeLogRepository;
use App\Repositories\Orders\Contracts\OrderGoodsRepository;
use App\Repositories\DailyNews\Contracts\NewsManageRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Goods\Contracts\GoodsRepository;

use App\Models\DpShopInfo;
use App\Models\DpDailyNewsInfo;
use App\Models\DpDailyNewsGoodsInfo;

use App\Services\DailyNews\Factory\GoodsPriceRiseOrDeclineFactory;
use App\Services\DailyNews\Factory\SendWeChatArticleFactory;

use App\Exceptions\User\UserNotExistsException;
use App\Exceptions\AppException;
use App\Exceptions\DailyNews\DailyNewsExceptionCode;
use App\Exceptions\Goods\GoodsExceptionCode;

use App\Events\RecommendGoodsUpdate;

use App\Utils\StringDisposeUtil;

/**
 * Class DailyNewsService.
 * 每日推送
 * @package App\Services
 */
class DailyNewsService
{
    private $messageSendUsersRepo;
    private $dailyNewsInfoRepo;
    private $dailyNewsLogRepo;
    private $dailyNewsGoodsRepo;
    private $orderGoodsRepo;
    private $newsManageRepo;
    private $goodsRepo;

    public function __construct(
        MessageSendUsersRepository $messageSendUsersRepo,
        DailyNewsInfoRepository $dailyNewsInfoRepo,
        DailyNewsLogRepository $dailyNewsLogRepo,
        DailyNewsGodsRepository $dailyNewsGoodsRepo,
        OrderGoodsRepository $orderGoodsRepo,
        NewsManageRepository $newsManageRepo,
        GoodsRepository $goodsRepo
    ) {
        $this->messageSendUsersRepo = $messageSendUsersRepo;
        $this->dailyNewsInfoRepo = $dailyNewsInfoRepo;
        $this->dailyNewsLogRepo = $dailyNewsLogRepo;
        $this->dailyNewsGoodsRepo = $dailyNewsGoodsRepo;
        $this->orderGoodsRepo = $orderGoodsRepo;
        $this->newsManageRepo = $newsManageRepo;
        $this->goodsRepo = $goodsRepo;
    }

    /**
     * 可接收客服消息推送的用户列表
     *
     * @param int $areaId 大区ID
     * @param int $page   当前页数
     * @param int $size   获取的数据量
     *
     * @return array
     */
    public function getDailyNewsReceiveUserList($areaId, $page, $size)
    {
        $messageSendUserInfoObjs = $this->messageSendUsersRepo->getDailyNewsReceiveUsers($areaId, $size);
        $reDataArr = [
            'page'      => (int)$page,
            'total'     => $messageSendUserInfoObjs->total(),
            'user_info' => [],
        ];
        if ($reDataArr['total']) {
            foreach ($messageSendUserInfoObjs as $userInfoObj) {
                if (count($userInfoObj->user) == 0) {
                    $userId = 0;
                    $userTel = '';
                    $weChatName = '';
                    $shopName = '';
                    $shopType = '';
                } else {
                    $userId = $userInfoObj->user[0]->shId;
                    $userTel = $userInfoObj->user[0]->lianxiTel;
                    $weChatName = $userInfoObj->user[0]->unionName;
                    $shopName = $userInfoObj->user[0]->shop->dianPuName;
                    $shopType = DpShopInfo::getShopTypeName($userInfoObj->user[0]->shop->trenchnum);
                }
                $reDataArr['user_info'][] = [
                    'user_id'       => $userId,
                    'user_tel'      => $userTel,
                    'wechat_openid' => $userInfoObj->we_chat_openid,
                    'wechat_name'   => $weChatName,
                    'shop_name'     => $shopName,
                    'shop_type'     => $shopType,
                    'interact_time' => $userInfoObj->updated_at,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 今日推送文章查询
     *
     * @param int $areaId 大区ID
     *
     * @return array
     */
    public function getTodayArticleList($areaId)
    {
        $todayArticleObjs = $this->dailyNewsInfoRepo->getTodayArticle($areaId);

        $reDataArr = [];
        if (!$todayArticleObjs->isEmpty()) {
            foreach ($todayArticleObjs as $articleObj) {
                $reDataArr[] = [
                    'area_id'       => $articleObj->area_id,
                    'article_id'    => $articleObj->id,
                    'article_type'  => $articleObj->news_type,
                    'article_title' => $articleObj->news_title,
                    'article_image' => $articleObj->news_images,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 每日推文类型获取
     *
     * @return array
     */
    public function getDailyNewsTypeList()
    {
        $reDataArr = [];
        $dailyNewsTypeArr = $this->dailyNewsInfoRepo->getDailyNewsTypeList();
        foreach ($dailyNewsTypeArr as $key => $value) {
            $reDataArr[] = [
                'id'   => $key,
                'name' => $value,
            ];
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reDataArr,
        ];
    }

    /**
     * 今日推文编辑（添加/修改）
     *
     * @param array $articleDataArrs 文章数据
     *                               0={
     *                               --area_id 大区ID
     *                               --article_id
     *                               --article_type
     *                               --article_title
     *                               --article_image
     *                               --article_order
     *                               }，
     *                               N={...}
     *
     * @return array
     * @throws AppException
     */
    public function editTodayArticle($articleDataArrs)
    {
        $self = $this;
        $newsTypeArr = array_column($articleDataArrs, 'article_type');
        $delNewsTypeArr = array_unique($newsTypeArr);
        if (count($newsTypeArr) != count($delNewsTypeArr)) {
            $repeatArr = array_diff_assoc($newsTypeArr, $delNewsTypeArr);
            throw new AppException(
                sprintf('今日推文类型 %s 已存在', DpDailyNewsInfo::getShowTypeName(current($repeatArr))),
                DailyNewsExceptionCode::REPEAT_ADD_ARTICLE
            );
        }
        DB::connection('mysql_zdp_main')->transaction(
            function () use ($self, $articleDataArrs) {
                $addArr = [];
                foreach ($articleDataArrs as $orderKey => $articleArr) {
                    $articleId = empty($articleArr['article_id']) ? 0 : $articleArr['article_id'];
                    $description = empty($articleArr['news_description']) ? '' : $articleArr['news_description'];
                    $sendDate = date('Y-m-d');
                    $linkUrl = config('request_url.wechat_request_url');
                    $new_title = StringDisposeUtil::englishSymbolsConversion($articleArr['article_title']);
                    if (empty($articleArr['article_id'])) {
                        // 没有文章ID 表示添加
                        $newDateTimeTxt = date('Y-m-d H:i:s');
                        $addArr[] = [
                            'area_id'          => $articleArr['area_id'],
                            'news_type'        => $articleArr['article_type'],
                            'news_title'       => $new_title,
                            'news_description' => $description,
                            'news_images'      => $articleArr['article_image'],
                            'url'              => $linkUrl,
                            'order_num'        => $orderKey,
                            'send_date'        => $sendDate,
                            'created_at'       => $newDateTimeTxt,
                            'updated_at'       => $newDateTimeTxt,
                        ];
                    } else {
                        // 文章ID存在，表示修改
                        $self->dailyNewsInfoRepo
                            ->updateArticle($articleId, $articleArr['article_type'], $new_title,
                                $description, $articleArr['article_image'], $orderKey
                            );
                    }
                }
                // 添加新的文章
                $self->dailyNewsInfoRepo->addTodayArticle($addArr);
            }
        );

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 今日推文删除
     *
     * @param int $articleId 文章ID
     *
     * @return array
     */
    public function delTodayArticle($articleId)
    {
        $this->dailyNewsInfoRepo->delTodayArticle($articleId);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 每日推文日志查询
     *
     * @param int    $areaId    大区ID
     * @param string $queryDate 查询年月 格式：2016-08
     *
     * @return array
     */
    public function getDailyNewsSendLog($areaId, $queryDate)
    {
        $sendLogInfoObjs = $this->dailyNewsLogRepo->getDailyNewsSendLog($areaId, $queryDate);
        $reData = [];
        if (!$sendLogInfoObjs->isEmpty()) {
            foreach ($sendLogInfoObjs as $logInfoObj) {
                $day = intval(substr($logInfoObj->send_date, -2));
                $reData[$day] = [
                    'date'            => $logInfoObj->send_date,
                    'delivery_number' => $logInfoObj->delivery_number,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 每日推文团购商品查询
     *
     * @param int $areaId 大区ID
     * @param int $page   当前页数
     * @param int $size   获取数据量
     *
     * @return array
     */
    public function getBulkPurchasingGoods($areaId, $page, $size)
    {
        $goodsInfoObjs = $this->dailyNewsGoodsRepo->getBulkPurchasingGoods($areaId, $size);
        $reData = [
            'page'       => (int)$page,
            'total'      => $goodsInfoObjs->total(),
            'goods_info' => [],
        ];
        if (!$goodsInfoObjs->isEmpty()) {
            foreach ($goodsInfoObjs as $key => $goodsObj) {
                // 昨日销量
                $yesterdaySalesNum = $this->orderGoodsRepo->getYesterdaySales($goodsObj->id);
                // 昨日价格
                $endPriceChangeDate = date('Y-m-d', strtotime($goodsObj->end_price_change_time));
                $yesterdayPrice = $this->getGoodsYesterdayPrice($goodsObj->id, $endPriceChangeDate,
                    $goodsObj->goods_price
                );
                $reData['goods_info'][] = [
                    'id'                  => $key,
                    'goods_id'            => $goodsObj->id,
                    'goods_name'          => $goodsObj->gname,
                    'yesterday_sales_num' => $yesterdaySalesNum,
                    'goods_price'         => $goodsObj->goods_price,
                    'yesterday_price'     => $yesterdayPrice,
                    'sell_shop_name'      => $goodsObj->dianPuName,
                    'shield_status'       => DpDailyNewsGoodsInfo::NOT_DELETE,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 每日推文新品或热门商品
     *
     * @param int $areaId        大区ID
     * @param int $articleTypeId 文章类型
     * @param int $page          当前页数
     * @param int $size          获取数量
     *
     * @return array
     */
    public function getNewProductOrHotSaleGoods($areaId, $articleTypeId, $page, $size)
    {
        $goodsInfoObjs = $this->dailyNewsGoodsRepo->getNewProductOrHotSaleGoods($areaId, $articleTypeId, $size);
        $reData = [
            'page'       => (int)$page,
            'total'      => $goodsInfoObjs->total(),
            'goods_info' => [],
        ];
        if (!$goodsInfoObjs->isEmpty()) {
            foreach ($goodsInfoObjs as $key => $goodsObj) {
                $shieldStatus = $goodsObj->trashed() ? DpDailyNewsGoodsInfo::DELETE : DpDailyNewsGoodsInfo::NOT_DELETE;
                $reData['goods_info'][] = [
                    'id'                  => $goodsObj->id,
                    'goods_id'            => $goodsObj->goods_id,
                    'goods_name'          => $goodsObj->goods->gname,
                    'yesterday_sales_num' => $goodsObj->yesterday_sales_num,
                    'goods_price'         => $goodsObj->goods->goodsAttribute->goods_price,
                    'yesterday_price'     => $goodsObj->yesterday_price,
                    'sell_shop_name'      => $goodsObj->goods->shop->dianPuName,
                    'shield_status'       => $shieldStatus,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 每日推文推荐商品列表
     *
     * @param int $areaId 大区ID
     * @param int $page   当前页数
     * @param int $size   获取数量
     *
     * @return array
     */
    public function getRecommendGoods($areaId, $page, $size)
    {
        $goodsInfoObjs = $this->dailyNewsGoodsRepo->getRecommendGoods($areaId, $size);
        $reData = [
            'page'       => (int)$page,
            'total'      => $goodsInfoObjs->total(),
            'goods_info' => [],
        ];
        if (!$goodsInfoObjs->isEmpty()) {
            foreach ($goodsInfoObjs as $key => $goodsObj) {
                // 昨日销量
                $yesterdaySalesNum = $this->orderGoodsRepo->getYesterdaySales($goodsObj->goods_id);
                // 昨日价格
                $endPriceChangeDate = date('Y-m-d', strtotime($goodsObj->goods->goodsAttribute->end_price_change_time));
                $yesterdayPrice = $this->getGoodsYesterdayPrice($goodsObj->goods_id, $endPriceChangeDate,
                    $goodsObj->goods->goodsAttribute->goods_price
                );
                $reData['goods_info'][] = [
                    'id'                  => $goodsObj->id,
                    'goods_id'            => $goodsObj->goods_id,
                    'goods_name'          => $goodsObj->goods->gname,
                    'yesterday_sales_num' => $yesterdaySalesNum,
                    'goods_price'         => $goodsObj->goods->goodsAttribute->goods_price,
                    'yesterday_price'     => $yesterdayPrice,
                    'sell_shop_name'      => $goodsObj->seller_shop_name,
                    'shield_status'       => DpDailyNewsGoodsInfo::NOT_DELETE,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 获取价格涨或跌的商品
     *
     * @param int    $areaId      大区ID
     * @param string $priceChange 涨价或降价标识
     * @param int    $page        当前页数
     * @param int    $size        获取数据量
     *
     * @return array
     * @throws App\Exceptions\AppException
     */
    public function getPriceRiseOrDeclineGoods($areaId, $priceChange, $page, $size)
    {
        // 根据 $priceChange 返回不同的操作实例
        $goodsInfoGetObj = GoodsPriceRiseOrDeclineFactory::getPriceRiseOrDeclineObj($priceChange);

        // 获取涨价或降价商品信息
        $goodsInfoObjs = $goodsInfoGetObj->getGoodsInfo($areaId, $size);

        $reData = [
            'page'       => (int)$page,
            'total'      => $goodsInfoObjs->total(),
            'goods_info' => [],
        ];
        if (!$goodsInfoObjs->isEmpty()) {
            foreach ($goodsInfoObjs as $key => $goodsObj) {
                $shieldStatus = $goodsObj->trashed() ? DpDailyNewsGoodsInfo::DELETE : DpDailyNewsGoodsInfo::NOT_DELETE;
                $reData['goods_info'][] = [
                    'id'                  => $goodsObj->id,
                    'goods_id'            => $goodsObj->goods_id,
                    'goods_name'          => $goodsObj->goods_name,
                    'yesterday_sales_num' => $goodsObj->yesterday_sell_num,
                    'goods_price'         => $goodsObj->now_price,
                    'yesterday_price'     => $goodsObj->yesterday_price,
                    'sell_shop_name'      => $goodsObj->supplier_name,
                    'shield_status'       => $shieldStatus,
                ];
            }
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 每日推文新品或热门商品屏蔽操作
     *
     * @param int $id           操作ID
     * @param int $goodsId      商品ID
     * @param int $shieldStatus 屏蔽类型
     *
     * @return array
     */
    public function shieldNewProductOrHotSaleGoods($id, $goodsId, $shieldStatus)
    {
        $this->dailyNewsGoodsRepo->shieldNewProductOrHotSaleGoods($id, $goodsId, $shieldStatus);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 屏蔽价格涨或跌的商品
     *
     * @param int    $id           操作ID
     * @param int    $goodsId      商品ID
     * @param int    $shieldStatus 屏蔽类型
     * @param string $priceChange  涨价或降价标识
     *
     * @return array
     * @throws App\Exceptions\AppException
     */
    public function shieldPriceRiseOrDeclineGoods($id, $goodsId, $shieldStatus, $priceChange)
    {
        // 根据 $priceChange 返回不同的操作实例
        $goodsInfoGetObj = GoodsPriceRiseOrDeclineFactory::getPriceRiseOrDeclineObj($priceChange);

        // 屏蔽涨价或降价商品信息
        $goodsInfoGetObj->shieldGoods($id, $goodsId, $shieldStatus);

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 获取每日推文管理信息
     *
     * @param int $areaId 大区ID
     *
     * @return array
     */
    public function getNewsManageInfo($areaId)
    {
        $manageInfoObj = $this->newsManageRepo->getDailyNewsManageInfo($areaId);
        $reData = [
            'id'             => 0,
            'edit_user_id'   => 0,
            'review_user_id' => 0,
            'send_time'      => '',
        ];
        if ($manageInfoObj) {
            $reData = [
                'id'             => $manageInfoObj->id,
                'edit_user_id'   => $manageInfoObj->edit_user_id,
                'review_user_id' => $manageInfoObj->review_user_id,
                'send_time'      => $manageInfoObj->send_time,
            ];
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $reData,
        ];
    }

    /**
     * 编辑每日推文管理信息
     *
     * @param int    $id           操作ID
     * @param int    $areaId       大区ID
     * @param int    $editUserId   编辑员ID
     * @param int    $reviewUserId 审核员ID
     * @param string $sendTime     发送时间 格式：H:i:s
     *
     * @return array
     * @throws UserNotExistsException
     */
    public function editNewsManageInfo($id, $areaId, $editUserId, $reviewUserId, $sendTime)
    {
        $userRepo = App::make(UserRepository::class);
        if ($editUserId > 0) {
            $editUserObj = $userRepo->getUserInfoById($editUserId);
            if (!$editUserObj) {
                throw new UserNotExistsException('编辑员不存在');
            }
        }
        if ($reviewUserId > 0) {
            $reviewUserObj = $userRepo->getUserInfoById($reviewUserId);
            if (!$reviewUserObj) {
                throw new UserNotExistsException('审核员不存在');
            }
        }

        if ($id == 0) {
            // 添加
            $this->newsManageRepo->addDailyNewsManageInfo($areaId, $editUserId, $reviewUserId, $sendTime);
        } else {
            // 修改
            $this->newsManageRepo->updateDailyNewsManageInfo($id, $areaId, $editUserId, $reviewUserId, $sendTime);
        }

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 推荐商品添加
     *
     * @param int $goodsId 商品ID
     *
     * @return array
     * @throws AppException
     */
    public function addRecommendGoods($goodsId)
    {
        // 获取需添加的商品是否已存在
        $recommendGoodsObj = $this->dailyNewsGoodsRepo->getRecommendGoodsByGoodsId($goodsId);
        if ($recommendGoodsObj) {
            throw new AppException('商品已在推荐中', DailyNewsExceptionCode::RECOMMEND_GOODS_EXIST);
        }
        // 获取商品的店铺及市场信息
        $goodsInfoObj = $this->goodsRepo->getGoodsInfoById($goodsId);
        if (!$goodsInfoObj) {
            throw new AppException('推荐的商品不存在或已删除', GoodsExceptionCode::GOODS_NOT);
        }
        // 添加推荐商品
        $this->dailyNewsGoodsRepo->addRecommendGoods($goodsId, $goodsInfoObj->shop->dianPuName,
            $goodsInfoObj->shop->market->divideid
        );

        // 触发事件处理缓存
        Event::fire(new RecommendGoodsUpdate($goodsInfoObj->shop->market->divideid));

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 删除单个推荐商品
     *
     * @param int $id 记录ID
     *
     * @return array
     */
    public function delRecommendGoods($id)
    {
        $this->dailyNewsGoodsRepo->delRecommendGoods($id);

        // 触发事件处理缓存
        Event::fire(new RecommendGoodsUpdate(0, $id));

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 删除所有推荐榜商品
     *
     * @return array
     */
    public function delRecommendGoodsAll()
    {
        $this->dailyNewsGoodsRepo->delRecommendGoodsAll();

        // 触发事件处理缓存
        Event::fire(new RecommendGoodsUpdate());

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 调整当前推荐榜商品排序
     *
     * @param int $currentId 调整记录的ID
     * @param int $nextId    调整后下一记录ID
     *
     * @return array
     */
    public function sortRecommendGoods($currentId, $nextId)
    {
        $this->dailyNewsGoodsRepo->sortRecommendGoods($currentId, $nextId);

        // 触发事件处理缓存
        Event::fire(new RecommendGoodsUpdate(0, $currentId));

        return [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];
    }

    /**
     * 推送每日文章给48小时内有过互动的微信会员(同时进行未编辑的审核的提醒)
     *
     * @return void
     */
    public function sendWeChatDailyArticle()
    {
        $currentTime = Carbon::now()->format('H:i');
        $dateTimeTxt = Carbon::now()->format('Y-m-d H:i:s');
        echo $dateTimeTxt;
        // 取得推送或检查的配置信息
        $sendManageInfoObjs = $this->newsManageRepo->getAllDailyNewsManageInfo();
        if (!$sendManageInfoObjs->isEmpty()) {
            // 需提醒的编辑员ID
            $editIdArr = [];
            // 需提醒的审核员ID
            $reviewIdArr = [];
            // 需推送的文章大区
            $sendAreaIdArr = [];
            foreach ($sendManageInfoObjs as $manageInfoObj) {
                $editRemindTime = empty($manageInfoObj->edit_remind_time)
                    ? '15:00'
                    : substr($manageInfoObj->edit_remind_time, 0, -3);
                $reviewRemindTime = empty($manageInfoObj->review_remind_time)
                    ? '16:00'
                    : substr($manageInfoObj->review_remind_time, 0, -3);
                $sendTime = substr($manageInfoObj->send_time, 0, -3);
                switch ($currentTime) {
                    case $editRemindTime:
                        // 获取是否有推送数据
                        $messageNum = $this->dailyNewsInfoRepo->getTodayArticleTotal($manageInfoObj->area_id);
                        if (empty($messageNum) && $manageInfoObj->edit_user_id > 0) {
                            $editIdArr[] = $manageInfoObj->edit_user_id;
                        }
                        break;
                    case $reviewRemindTime:
                        // 获取是否有推送数据
                        $messageNum = $this->dailyNewsInfoRepo->getTodayArticleTotal($manageInfoObj->area_id);
                        if (empty($messageNum) && $manageInfoObj->review_user_id > 0) {
                            $reviewIdArr[] = $manageInfoObj->review_user_id;
                        }
                        break;
                    case $sendTime:
                        // 获取是否有推送数据
                        $messageNum = $this->dailyNewsInfoRepo->getTodayArticleTotal($manageInfoObj->area_id);
                        if ($messageNum) {
                            $sendAreaIdArr[] = $manageInfoObj->area_id;
                        }
                        break;
                }
            }
            /** @var SendWeChatArticleFactory $sendMessageObj */
            $sendMessageObj = App::make(SendWeChatArticleFactory::class);
            $sendMessageObj->getSendObj($editIdArr, $reviewIdArr, $sendAreaIdArr);
        } else {
            echo '--每日推送管理信息未配置' . PHP_EOL;
            exit;
        }
    }

    /**
     * 返回商品的昨日价格
     *
     * @param int    $goodsId            商品ID
     * @param string $endPriceChangeDate 最后改价日期
     * @param float  $goodsPrice         商品价格
     *
     * @return float
     */
    private static function getGoodsYesterdayPrice($goodsId, $endPriceChangeDate, $goodsPrice)
    {
        $dayDate = Carbon::now()->format('Y-m-d');
        $todayPrice = $goodsPrice;
        if ($endPriceChangeDate == $dayDate) {
            // 当天改过价，取得当天最早的一次改价价格
            $goodsPriceChangeLogRepo = App::make(GoodsPriceChangeLogRepository::class);
            $todayPrice = $goodsPriceChangeLogRepo->getGoodsDayStartChangePrice($goodsId, $dayDate);
        }

        return $todayPrice;
    }

    /**
     * 生成今日推文文章的链接地址，目前没有使用，发送消息的时候再进行拼装
     *
     * @param int    $articleType 文章类型
     * @param string $showDate    查询日期
     *
     * @return string
     */
    private static function getTodayArticleLinkUrl($articleType, $showDate = '')
    {
        $wechatUrl = config('request_url.wechat_request_url');
        switch ($articleType) {
            case DpDailyNewsInfo::BULK_PURCHASING:
                return $wechatUrl . '/?pageTag=' . $articleType;
                break;
            case DpDailyNewsInfo::NEW_PRODUCT:
                return $wechatUrl . '/?pageTag=' . $articleType . '&showDate=' . $showDate;
                break;
            case DpDailyNewsInfo::HOT_SALE:
                return $wechatUrl . '/?pageTag=' . $articleType . '&showDate=' . $showDate;
                break;
            case DpDailyNewsInfo::PRICE_LIST:
                return $wechatUrl . '/?pageTag=' . $articleType . '&showDate=' . $showDate;
                break;
        }

        return $wechatUrl;
    }
}
