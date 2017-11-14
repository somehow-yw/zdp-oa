<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 2016/11/29
 * Time: 下午10:56
 */

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Services\Searches\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SearchConfigController extends Controller
{

    /**
     * 默认搜索排序权重配置
     */
    const DEFAULT_SORT_BOOST = [
        'score'          => 51,
        'price_expired'  => 25,
        'shop_level'     => 4,
        'appraise_rate'  => 5,
        'appraise_num'   => 5,
        'order_num'      => 5,
        'has_inspection' => 2,
        'shop_violation' => 3,
    ];

    /**
     * 默认的搜索排序权重配置的存储位置
     */
    const SORT_BOOST_CONF = 'search/config/sort_boost.json';

    /**
     * 更新同义词
     *
     * @param Request       $request
     * @param SearchService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSearchSynonym(Request $request, SearchService $service)
    {
        $this->validate(
            $request,
            [
                'synonym' => 'required|string',
            ],
            [
                'synonym.required' => '同义词不能为空',
                'synonym.string'   => '同义词必须是个字符串',
            ]
        );
        $synonymStr = $request->input('synonym');
        $service->updateSearchSynonym($synonymStr);

        return $this->render(
            'search.list',
            [],
            'OK',
            0
        );
    }

    /**
     * 更新自定义词典
     *
     * @param Request       $request
     * @param SearchService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCustomDict(Request $request, SearchService $service)
    {
        $this->validate(
            $request,
            [
                'dict' => 'required|string',
            ],
            [
                'dict.required' => '词典不能为空',
                'dict.string'   => '词典必须是个字符串',
            ]
        );
        $dict = $request->input('dict');
        $service->updateCustomDict($dict);

        return $this->render(
            'search.list',
            [],
            'OK',
            0
        );
    }

    /**
     * 更新搜索排序的权重配置
     *
     * @param Request $request
     *
     * @return string json template
     */
    public function updateGoodsSortBoost(Request $request)
    {
        $rules = [];
        foreach (self::DEFAULT_SORT_BOOST as $n => $v) {
            $rules[$n] = 'required|int';
        }

        $this->validate(
            $request,
            $rules,
            [
                'required' => '配置 :attribute 不可为空',
                'int'      => '配置 :attribute 必须是一个整数',
            ]
        );

        $boost = array_filter(
            $request->input(),
            function ($key) {
                return array_key_exists($key, self::DEFAULT_SORT_BOOST);
            },
            ARRAY_FILTER_USE_KEY
        );

        $ret = \Storage::disk('aliyun_private')->put(self::SORT_BOOST_CONF, json_encode($boost));

        if (!$ret) {
            return $this->renderError('写入配置失败');
        }

        return $this->render(
            'search.list',
            [],
            'OK',
            0
        );
    }

    /**
     * 获取搜索排序的权重配置
     *
     * @param Request $request
     *
     * @return string json template
     */
    public function getGoodsSortBoost(Request $request)
    {
        $fs = \Storage::disk('aliyun_private');
        if (!$fs->exists(self::SORT_BOOST_CONF)) {
            return self::DEFAULT_SORT_BOOST;
        }

        $online = @json_decode($fs->get(self::SORT_BOOST_CONF), true);
        if (empty($online)) {
            return self::DEFAULT_SORT_BOOST;
        }

        return $this->render(
            'search.list',
            [
                'boost' => array_merge(self::DEFAULT_SORT_BOOST, $online),
                'tip'   => [
                    'score'          => '搜索相关度',
                    'price_expired'  => '价格是否过期',
                    'shop_level'     => '店铺等级',
                    'appraise_rate'  => '好评率',
                    'appraise_num'   => '好评数',
                    'order_num'      => '订单数量',
                    'has_inspection' => '是否有检验报告',
                    'shop_violation' => '店铺违规率',
                ],
            ],
            'OK',
            0
        );
    }

    /**
     * 重建索引
     */
    public function dictInit()
    {
        //No lock for dict init, return ok instantly,process dict init logic in middleware
        return $this->render(
            'search.list',
            [],
            'OK'
        );
    }

    /**
     *  搜索索引重建
     */
    public function indexInit()
    {
        //No lock for index init, return ok instantly,process index init logic in middleware
        return $this->render(
            'search.list',
            [],
            'OK'
        );
    }
}
