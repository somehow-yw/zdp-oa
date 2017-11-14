<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/26/16
 * Time: 4:04 PM
 */

namespace intg\App\Http\Controllers;


use intg\App\TestCase;
use App\Models\DpBrands;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class BrandsTest extends TestCase
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
     * 测试获取品牌列表接口
     */
    function testGetBrandsList()
    {
        //正常读取
        $params = [];
        $params['page'] = 1;
        $params['size'] = 10;
        $response = $this->getBrandsListRequest($params);
        $this->assertSuccess($response['code']);
        $this->assertData($response['data']);
        $this->assertBrands($response['data']);

        //越界参数
        $params['page'] = 5;
        $params['size'] = -10;
        $response = $this->getBrandsListRequest($params);
        $this->assertFailure($response['code']);

        //缺参数
        $params['page'] = 5;
        $response = $this->getBrandsListRequest($params);
        $this->assertFailure($response['code']);
    }

    /**
     * 测试添加品牌接口
     */
    function testAddBrand()
    {
        //正常测试
        $params = [];
        $params ['brand'] = "品牌测试";
        $params ['key_words'] = "好吃,不贵,划算,鸡胸";
        $response = $this->addBrandRequest($params);

        $this->assertSuccess($response['code']);
        $this->assertStoreSuccess($params['brand']);

        //重复添加
        $params = [];
        $params ['brand'] = "品牌测试";
        $params ['key_words'] = "好吃,不贵,划算,鸡胸";
        $response = $this->addBrandRequest($params);
        $this->assertFailure($response['code']);

        //无效参数(参数过长)
        $params = [];
        $params ['brand'] = "品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试品牌测试";
        $params ['key_words'] = "好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸好吃,不贵,划算,鸡胸";
        $response = $this->addBrandRequest($params);
        $this->assertFailure($response['code']);

        //缺参数
        $params = [];
        $params ['brand'] = "品牌测试";
        $response = $this->addBrandRequest($params);
        $this->assertFailure($response['code']);
    }

    /**
     * 测试更新品牌接口
     */
    function testUpdateBrand()
    {
        //添加品牌
        $params = [
            'brand'     => '美好火腿',
            'key_words' => '火腿,美好',
        ];
        $response = $this->addBrandRequest($params);
        $this->assertSuccess($response['code']);
        $this->assertStoreSuccess($params['brand']);
        $brand = DpBrands::where('brand', '=', $params['brand'])->first();
        //修改品牌
        $params = [
            'id'        => $brand->id,
            'brand'     => '双汇火腿',
            'key_words' => '双汇,火腿'
        ];
        $response = $this->updateBrandRequest($params);
        $this->assertSuccess($response['code']);
        $this->assertStoreSuccess($params['brand']);
        //缺参数
        $params = [
            'brand'     => '双汇火腿',
            'key_words' => '双汇,火腿'
        ];
        $response = $this->updateBrandRequest($params);
        $this->assertFailure($response['code']);
    }

    /**
     * 测试删除品牌接口
     */
    function testDeleteBrand()
    {
        $params = [
            'brand'     => '盼盼食品',
            'key_words' => '盼盼,小面包',
        ];
        $response = $this->addBrandRequest($params);
        $this->assertSuccess($response['code']);
        $this->assertStoreSuccess($params['brand']);
        $brand = $brand = DpBrands::where('brand', '=', $params['brand'])->first();

        $params = [
            'id' => $brand->id
        ];
        $response = $this->deleteBrandRequest($params);
        $this->assertSuccess($response['code']);
        $this->assertSoftDelete($params['id']);
    }

    /**
     * 测试搜索品牌接口
     */
    function testSearchBrand()
    {
        //正常请求
        $params = [];
        $params['brand'] = 'c';
        $params['size'] = 10;
        $params['page'] = 1;
        $response = $this->searchBrandRequest($params);
        $this->assertSuccess($response['code']);
        $this->assertData($response['data']);
        $this->assertBrands($response['data']);
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
     * @param $data array response data
     */
    private function assertData($data)
    {
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('brands', $data);
    }

    /**
     * @param $data array response data
     */
    private function assertBrands($data)
    {
        $brands = $data['brands'];
        foreach ($brands as $brand) {
            $this->assertArrayHasKey('id', $brand);
            $this->assertArrayHasKey('brand', $brand);
            $this->assertArrayHasKey('key_words', $brand);
        }
    }

    /**
     * @param $brand string brand
     */
    private function assertStoreSuccess($brand)
    {
        $this->seeInDatabase('dp_brands', ['brand' => $brand]);
    }

    /**
     * @param $brand integer brand id
     */
    private function assertSoftDelete($id)
    {
        //因为是软删除 所以不能用notSeeInDatabase
        $count = DpBrands::onlyTrashed()->where('id', '=', $id)->count();
        $this->assertGreaterThan(0, $count);
    }

    /**
     * @param $params array
     * @return array
     */
    private function getBrandsListRequest($params)
    {
        $response = $this
            ->jsonGet('/goods/brands/list', $params)
            ->response->getContent();
        $this->isJson($response);

        return json_decode($response, true);
    }

    /**
     * @param $params array
     * @return array
     */
    private function deleteBrandRequest($params)
    {
        $response = $this
            ->jsonPost('/goods/brands/delete', $params)
            ->response->getContent();
        $this->isJson($response);

        return json_decode($response, true);
    }

    /**
     * @param $params
     * @return array
     */
    private function addBrandRequest($params)
    {
        $response = $this
            ->jsonPost('/goods/brands/add', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }

    /**
     * @param $params
     * @return array
     */
    private function updateBrandRequest($params)
    {
        $response = $this
            ->jsonPost('/goods/brands/update', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }

    /**
     * @param $params
     * @return array
     */
    private function searchBrandRequest($params)
    {
        $response = $this
            ->jsonGet('/goods/brands/list', $params)
            ->response->getContent();

        $this->isJson($response);

        return json_decode($response, true);
    }
}