<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/29/16
 * Time: 3:41 PM
 */

namespace intg\App\Http\Controllers;


use App\Repositories\Goods\ActivityGoodsRepository;
use App\Repositories\Goods\ActivityRepository;
use Illuminate\Support\Facades\App;
use intg\App\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ActivityTest extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;

    function createApplication()
    {
        //设置当前数据库连接,以便测试数据库回滚
        putenv('DB_CONNECTION=mysql_zdp_main');

        return parent::createApplication();
    }

    /**
     * 测试获取活动列表
     */
    function testGetActivitiesList()
    {
        //warning 没有自动去做数据填充

        //正常请求
        $params = [
            'area_id'          => 2,
            'activity_type_id' => 4
        ];
        $response = $this->requestGetActivitiesList($params);
        $this->assertSuccess($response['code']);
        //area_id 不是整数
        $params = [
            'area_id'          => 1.1,
            'activity_type_id' => 4
        ];
        $response = $this->requestGetActivitiesList($params);
        $this->assertFailure($response['code']);
        //area_id 不存在
        $params = [
            'area_id'          => 1,
            'activity_type_id' => 4
        ];
        $response = $this->requestGetActivitiesList($params);
        $this->assertFailure($response['code']);
        //activity_type_id 没数据
        $params = [
            'area_id'          => 2,
            'activity_type_id' => 3
        ];
        $response = $this->requestGetActivitiesList($params);
        $this->assertEquals(0, count($response['data']['activities']));


    }

    /**
     * 测试添加活动
     */
    function testAddActivity()
    {
//        $params = [
//            'data' => [
//                'area_id'       => '2',
//                'start_time'    => '2016-09-29 11:42:00',
//                'end_time'      => '2016-09-29 11:50:00',
//                'shop_type_ids' => [1, 3, 4]
//            ]
//        ];
//
//        $response = $this->requestAddActivity($params);

    }

    /**
     * @param $code integer code
     */
    private function assertFailure($code)
    {
        $this->assertNotEquals(0, $code);
    }

    /**
     * @param $code integer code
     */
    private function assertSuccess($code)
    {
        $this->assertEquals(0, $code);
    }

    /**
     * 请求获取活动列表
     *
     * @param $params array 参数
     * @return array
     */
    function requestGetActivitiesList($params)
    {
        $response = $this
            ->jsonGet('/activities/list', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }

    function testActivityRepo()
    {
        /** @var ActivityGoodsRepository $repo */
        $repo = App::make(\App\Repositories\Goods\Contracts\ActivityGoodsRepository::class);
        $repo->getActivityGoodsList(2, 10);
    }

    /**
     * 请求添加活动
     *
     * @param $params array 参数
     * @return array
     */
    function requestAddActivity($params)
    {
        $response = $this
            ->jsonPost('/activities/add', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }
}