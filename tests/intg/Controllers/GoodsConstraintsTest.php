<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/27/16
 * Time: 8:21 PM
 */

namespace intg\App\Http\Controllers;


use App\Models\DpGoodsConstraints;
use intg\App\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GoodsConstraintsTest extends TestCase
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
     * 测试获取商品分类基本属性
     */
    function testGetGoodsBasicAttr()
    {
        $type_id = 1;
        $constraint_type = DpGoodsConstraints::TYPE_CONSTRAINT;
        $format_type_id = 0;
        $format_values = json_encode([[
            'value'   => 10,
            'unit'    => '斤',
            'default' => true,
        ]]);
        $format_rule = '{"spec":"required|float|max:20"}';
        $attr = compact('type_id', 'constraint_type', 'format_type_id', 'format_rule', 'format_values');
        $type_constraint = new DpGoodsConstraints($attr);
        $type_constraint->save();
        $constraint_type = DpGoodsConstraints::SPEC_CONSTRAINT;
        $format_type_id = 2;
        $format_values = json_encode([
            [
                'value'   => 20,
                'unit'    => '斤',
                'default' => true,
            ],
            [
                'value'   => 30,
                'unit'    => '斤',
                'default' => true,
            ],
        ]);
        $attr = compact('type_id', 'constraint_type', 'format_type_id', 'format_rule', 'format_values');
        $spec_constraint = new DpGoodsConstraints($attr);
        $spec_constraint->save();

        //正常请求
        $params = ['type_id' => 1];
        $response = $this->requestGetGoodsBasicAttr($params);


        $this->assertSuccess($response['code']);
        $this->assertData($response['data']);

        //type_id不存在
        $params = ['type_id' => 999999999];
        $response = $this->requestGetGoodsBasicAttr($params);
        $this->assertFailure($response['code']);

    }

    /**
     * 测试更新商品分类基本属性接口
     */
    function testUpdateGoodsBasicAttr()
    {
        $params = <<<json
{
    "data": {
        "type_id":1,
        "type_constraint": {
            "format_type_id": 1,
            "format_values": [
                {
                    "value": 10,
                    "unit": "袋每箱",
                    "default": true,
                    "rule":"integer"
                }
            ]
        },
        "spec_constraint": {
            "format_type_id": 2,
            "format_values": [
                {
                    "value": 20,
                    "unit": "斤",
                    "default": false,
                    "rule":"double"
                },
                {
                    "value": 30,
                    "unit": "斤",
                    "default": false,
                    "rule":"double"
                }
            ]
        }
    }
}
json;
        $params = json_decode($params, true);
        $response = $this->requestUpdateGoodsBasicAttr($params);
        echo $response['message'];
        $this->assertSuccess($response['code']);


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
     * assert data has specific key
     * @param $data
     */
    private function assertData($data)
    {
        $this->assertArrayHasKey('type_constraint', $data);
        $this->assertArrayHasKey('spec_constraint', $data);
        if (count($data['spec_constraint']) > 0) {
            $this->assertArrayHasKey('format_type_id', $data['spec_constraint']);
            $this->assertArrayHasKey('format_value', $data['spec_constraint']);
            $values = $data['spec_constraint']['format_value'];
            if (count($values) > 0) {
                foreach ($values as $value) {
                    $this->assertArrayHasKey('value', $value);
                    $this->assertArrayHasKey('unit', $value);
                    $this->assertArrayHasKey('default', $value);
                }
            }
        }

        if (count($data['type_constraint']) > 0) {
            $this->assertArrayHasKey('format_type_id', $data['type_constraint']);
            $this->assertArrayHasKey('format_value', $data['type_constraint']);
            $values = $data['type_constraint']['format_value'];
            if (count($values) > 0) {
                foreach ($values as $value) {
                    $this->assertArrayHasKey('value', $value);
                    $this->assertArrayHasKey('unit', $value);
                    $this->assertArrayHasKey('default', $value);
                }
            }
        }
    }

    /**
     * 请求获取商品分类基本属性
     *
     * @param $params
     * @return array
     */
    function requestGetGoodsBasicAttr($params)
    {
        $response = $this
            ->jsonGet('/goods/type/basic-attr/get', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }

    function requestUpdateGoodsBasicAttr($params)
    {
        $response = $this
            ->jsonPost('/goods/type/basic-attr/update', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }
}