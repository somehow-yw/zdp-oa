<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/8/27
 * Time: 17:18
 */

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Services\DailyNewsService;
use App\Workflows\DailyNewsWorkflow;

class DailyNewsController extends Controller
{
    /**
     * 可接收客服消息推送的用户列表
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getDailyNewsReceiveUserList(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1',
                'page'    => 'required|integer|between:1,99999',
                'size'    => 'required|integer|between:1,100',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',
            ]
        );

        $reData = $dailyNewsService->getDailyNewsReceiveUserList(
            $request->input('area_id'), $request->input('page'), $request->input('size')
        );

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 今日推送文章查询
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getTodayArticleList(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',
            ]
        );

        $reData = $dailyNewsService->getTodayArticleList($request->input('area_id'));

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 今日推文编辑（添加/修改）
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function editTodayArticle(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'data' => 'required|string|between:5,5000',
            ],
            [
                'data.required' => '文章数据必须有',
                'data.integer'  => '文章数据应该是一个合法的JSON串',
                'data.between'  => '文章数据长度应在:min到:max之间',
            ]
        );

        $articleDataArrs = json_decode($request->input('data'), true);
        if (json_last_error()) {
            $reData = [
                'code'    => json_last_error(),
                'message' => '不是一个合法的JSON串-' . json_last_error_msg(),
                'data'    => [],
            ];
        }

        // 验证参数
        $messages = $this->editTodayArticleDate($articleDataArrs);
        if (count($messages)) {
            return $this->renderError($messages[0]);
        }

        $reData = $dailyNewsService->editTodayArticle($articleDataArrs);

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 今日推文删除
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function delTodayArticle(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'article_id' => 'required|integer|min:1',
            ],
            [
                'article_id.required' => '文章ID必须有',
                'article_id.integer'  => '文章ID必须是一个整型',
                'article_id.min'      => '文章ID应不小于:min',
            ]
        );

        $reData = $dailyNewsService->delTodayArticle($request->input('article_id'));

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 每日推送日志查询
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getDailyNewsSendLog(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1',
                'date'    => 'required|date_format:Y-m',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',

                'date.required'    => '查询年月必须有',
                'date.date_format' => '查询年月格式错误，正确如：2016-08',
            ]
        );

        $reData = $dailyNewsService->getDailyNewsSendLog($request->input('area_id'), $request->input('date'));

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 今日推送商品查询
     *
     * @param Request           $request
     * @param DailyNewsWorkflow $dailyNewsWorkflow
     *
     * @return \Illuminate\Http\Response
     */
    public function getTodaySendGoodsList(
        Request $request,
        DailyNewsWorkflow $dailyNewsWorkflow
    ) {
        $this->validate(
            $request,
            [
                'area_id'         => 'required|integer|min:1',
                'article_type_id' => 'required|integer|min:1',
                'price_change'    => 'string|between:1,50',
                'page'            => 'required|integer|between:1,99999',
                'size'            => 'required|integer|between:1,100',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',

                'article_type_id.required' => '文章类型必须有',
                'article_type_id.integer'  => '文章类型必须是一个整数',
                'article_type_id.min'      => '文章类型不可小于:min',

                'price_change.string'  => '价格变化必须是一个字符串',
                'price_change.between' => '价格变化数据长度必须在:min, 到:max间',

                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',
            ]
        );

        $priceChange = $request->input('price_change', '');
        $reData = $dailyNewsWorkflow->getTodaySendGoodsList($request->input('area_id'),
            $request->input('article_type_id'), $request->input('page'), $request->input('size'), $priceChange
        );

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 今日推送商品屏蔽操作
     *
     * @param Request           $request
     * @param DailyNewsWorkflow $dailyNewsWorkflow
     *
     * @return \Illuminate\Http\Response
     */
    public function shieldTodaySendGoods(
        Request $request,
        DailyNewsWorkflow $dailyNewsWorkflow
    ) {
        $this->validate(
            $request,
            [
                'id'              => 'required|integer|min:1',
                'goods_id'        => 'required|integer|min:1',
                'article_type_id' => 'required|integer|min:1',
                'shield_status'   => 'required|integer|between:1,2',
                'price_change'    => 'string|between:1,50',
            ],
            [
                'id.required' => '操作序号必须有',
                'id.integer'  => '操作序号应该是一个整数',
                'id.min'      => '操作序号不可小于:min',

                'goods_id.required' => '商品ID必须有',
                'goods_id.integer'  => '商品ID必须是一个整数',
                'goods_id.min'      => '商品ID不可小于:min',

                'article_type_id.required' => '文章类型必须有',
                'article_type_id.integer'  => '文章类型必须是一个整数',
                'article_type_id.min'      => '文章类型不可小于:min',

                'shield_status.required' => '屏蔽操作类型必须有',
                'shield_status.integer'  => '屏蔽操作类型必须是一个整型',
                'shield_status.between'  => '屏蔽操作类型必须是:min, 到:max的整数',

                'price_change.string'  => '价格变化必须是一个字符串',
                'price_change.between' => '价格变化数据长度必须在:min, 到:max间',
            ]
        );

        $priceChange = $request->input('price_change', '');
        $reData = $dailyNewsWorkflow->shieldTodaySendGoods($request->input('id'), $request->input('goods_id'),
            $request->input('article_type_id'), $request->input('shield_status'), $priceChange
        );

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 获取每日推文管理信息
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function getNewsManageInfo(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'area_id' => 'required|integer|min:1',
            ],
            [
                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',
            ]
        );

        $reData = $dailyNewsService->getNewsManageInfo($request->input('area_id'));

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 编辑每日推文管理信息
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function editNewsManageInfo(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'id'             => 'integer|min:0',
                'area_id'        => 'required|integer|min:1',
                'edit_user_id'   => 'required|integer|min:0',
                'review_user_id' => 'required|integer|min:0',
                'send_time'      => 'string|date_format:H:i:s',
            ],
            [
                'id.integer' => '操作ID应该是一个整型',
                'id.min'     => '操作ID不可小于:min',

                'area_id.required' => '大区ID必须有',
                'area_id.integer'  => '大区ID应该是一个整型',
                'area_id.min'      => '大区ID不可小于:min',

                'edit_user_id.required' => '编辑员ID必须有',
                'edit_user_id.integer'  => '编辑员ID应该是一个整型',
                'edit_user_id.min'      => '编辑员ID不可小于:min',

                'review_user_id.required' => '审核员ID必须有',
                'review_user_id.integer'  => '审核员ID应该是一个整型',
                'review_user_id.min'      => '审核员ID不可小于:min',

                'send_time.string'      => '发送时间必须是字符串',
                'send_time.date_format' => '发送时间格式应该如：21:00:00',
            ]
        );

        $id = $request->input('id', 0);
        $sendTime = $request->has('send_time') ? $request->input('send_time') : '21:00:00';
        $reData = $dailyNewsService->editNewsManageInfo(
            $id,
            $request->input('area_id'),
            $request->input('edit_user_id', 0),
            $request->input('review_user_id', 0),
            $sendTime
        );

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 推荐商品添加
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\User\UserNotExistsException
     */
    public function addRecommendGoods(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|integer|min:1',
            ],
            [
                'goods_id.required' => '商品ID必须有',
                'goods_id.integer'  => '商品ID应该是一个整型',
                'goods_id.min'      => '商品ID不可小于:min',
            ]
        );

        $reData = $dailyNewsService->addRecommendGoods($request->input('goods_id'));

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 删除单个推荐商品
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function delRecommendGoods(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|min:1',
            ],
            [
                'id.required' => '列表ID必须有',
                'id.integer'  => '列表ID应该是一个整型',
                'id.min'      => '列表ID不可小于:min',
            ]
        );

        $reData = $dailyNewsService->delRecommendGoods($request->input('id'));

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 删除所有推荐榜商品
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function delRecommendGoodsAll(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $reData = $dailyNewsService->delRecommendGoodsAll();

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 调整当前推荐榜商品排序
     *
     * @param Request          $request
     * @param DailyNewsService $dailyNewsService
     *
     * @return \Illuminate\Http\Response
     */
    public function sortRecommendGoods(
        Request $request,
        DailyNewsService $dailyNewsService
    ) {
        $this->validate(
            $request,
            [
                'current_id' => 'required|integer|min:1',
                'next_id'    => 'required|integer|min:1',
            ],
            [
                'current_id.required' => '调整记录的列表ID必须有',
                'current_id.integer'  => '调整记录的列表ID应该是一个整型',
                'current_id.min'      => '调整记录的列表ID不可小于:min',

                'next_id.required' => '调整后下一个记录的列表ID必须有',
                'next_id.integer'  => '调整后下一个记录的列表ID应该是一个整型',
                'next_id.min'      => '调整后下一个记录的列表ID不可小于:min',
            ]
        );

        $reData = $dailyNewsService->sortRecommendGoods($request->input('current_id'),
            $request->input('next_id', 0)
        );

        return $this->render(
            'operate.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 今日推文编辑请求参数验证
     *
     * @param array $dateArrs 需验证的参数数组 格式：
     *                        0=[
     *                        area_id int 大区ID
     *                        article_id int 文章ID
     *                        article_type int 文章类型
     *                        article_title string 文章标题
     *                        article_image string 文章图片地址
     *                        article_order int 文章顺序
     *                        ]
     *                        N=[...]
     *
     * @return array|mixed
     */
    private function editTodayArticleDate($dateArrs)
    {
        $key = 1;
        foreach ($dateArrs as $value) {
            $validator = Validator::make(
                $value,
                [
                    'area_id'       => 'required|integer|min:1',
                    'article_id'    => 'integer|min:0',
                    'article_type'  => 'required|integer|between:1,8',
                    'article_title' => 'required|string|between:1,255',
                    'article_image' => 'required|string|between:5,255',
                ],
                [
                    'area_id.required' => $key . '-大区ID必须有',
                    'area_id.integer'  => $key . '-大区ID必须是一个整型',
                    'area_id.min'      => $key . '-大区ID应不小于:min',

                    'article_id.integer' => $key . '-文章ID必须是一个整型',
                    'article_id.min'     => $key . '-文章ID应不小于:min',

                    'article_type.required' => $key . '-文章类型必须有',
                    'article_type.integer'  => $key . '-文章类型必须是一个整型',
                    'article_type.between'  => $key . '-文章类型必须是:min, 到:max的整数',

                    'article_title.required' => $key . '-文章标题必须有',
                    'article_title.string'   => $key . '-文章标题必须是一个字符串',
                    'article_title.between'  => $key . '-文章标题长度必须在:min, 到:max之间',

                    'article_image.required' => $key . '-文章图片必须有',
                    'article_image.integer'  => $key . '-文章图片必须是一个字符串',
                    'article_image.between'  => $key . '-文章图片长度必须在:min, 到:max之间',
                ]
            );
            $errors = $validator->errors();
            if (!$errors->isEmpty()) {
                $message = current(current($errors));

                return $message;
            }
            $key++;
        }

        return [];
    }
}