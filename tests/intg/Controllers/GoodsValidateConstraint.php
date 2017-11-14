<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/10/16
 * Time: 11:49 AM
 */

namespace intg\App\Http\Controllers;

use App\Exceptions\AppException;
use App\Http\Controllers\GoodsController;
use App\Models\DpGoodsConstraints;
use App\Services\Goods\GoodsConstraintsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\App;
use intg\App\TestCase;

/**
 * Class GoodsValidateConstraint
 *
 * @package intg\App\Http\Controllers
 */
class GoodsValidateConstraint extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;
    /** @var  $goodsController GoodsController */
    protected $goodsController;

    public function createApplication()
    {
        //设置当前数据库连接,以便测试数据库回滚
        putenv('DB_CONNECTION=mysql_zdp_main');

        return parent::createApplication();
    }

    public function testValidateText()
    {
        $this->goodsController = App::make(GoodsController::class);

        $values = [1];
        $rules = ["integer"];
        $this->goodsController->validateText($values, $rules);
        try {
            $values = [1.1];
            $this->goodsController->validateText($values, $rules);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("类型约束值必须为整数", $e->getMessage());
        }
        try {
            $values = [1, 2];
            $this->goodsController->validateText($values, $rules);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("对于文本框只能有一个值", $e->getMessage());
        }
    }

    public function testValidateRadio()
    {
        $this->goodsController = App::make(GoodsController::class);

        $values = ["黑"];
        $formatValues = ["黑", "红", "绿"];

        $this->goodsController->validateRadio($values, $formatValues);

        //该选项不在选项中
        try {
            $values = ["粉"];
            $this->goodsController->validateRadio($values, $formatValues);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("该单选选项不存在", $e->getMessage());
        }
        //只能为一个选项
        try {
            $values = ["黑", "红"];
            $this->goodsController->validateRadio($values, $formatValues);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("对于单选框有且只能有一个值", $e->getMessage());
        }
    }

    public function testValidateCheckbox()
    {
        $this->goodsController = App::make(GoodsController::class);
        $values = ["红", "粉", "黄"];
        $formatValues = ["黑", "红", "绿", "粉", "黄", "蓝", "灰"];
        $this->goodsController->validateCheckbox($values, $formatValues);

        //某选项不在其中
        try {
            $values = ["红", "紫", "黄"];
            $this->goodsController->validateCheckbox($values, $formatValues);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("紫不在多选选项中", $e->getMessage());
        }
        //空选项
        try {
            $values = [];
            $this->goodsController->validateCheckbox($values, $formatValues);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("多选框至少得选择一项", $e->getMessage());
        }
    }

    public function testValidateXY()
    {
        $this->goodsController = App::make(GoodsController::class);
        $values = [5, 10];
        $rules = ["integer", "integer"];
        $this->goodsController->validateXY($values, $rules);

        //Y规则不匹配
        try {
            $values = [5, 11.1];
            $rules = ["integer", "integer"];
            $this->goodsController->validateXY($values, $rules);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("类型约束值必须为整数", $e->getMessage());
        }

        //X规则不匹配
        try {
            $values = ["hello", 11];
            $rules = ["numeric", "integer"];
            $this->goodsController->validateXY($values, $rules);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("类型约束值必须为数字", $e->getMessage());
        }
        //少值
        try {
            $values = [11];
            $rules = ["integer"];
            $this->goodsController->validateXY($values, $rules);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("XY型的值必须为两个", $e->getMessage());
        }
    }

    public function testValidateConstraint()
    {
        $this->goodsController = App::make(GoodsController::class);
        $service = App::make(GoodsConstraintsService::class);
        //测试文本框
        function testText($controller, $values, $service)
        {
            $controller->validateConstraint(1, DpGoodsConstraints::TYPE_CONSTRAINT, $values, $service);
        }

        $values = [1];
        testText($this->goodsController, $values, $service);
        //约束规则不匹配---类型错误
        try {
            $values = [1.11];
            testText($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("类型约束值必须为整数", $e->getMessage());
        }
        //约束规则不匹配---参数个数不符合
        try {
            $values = [1, 2, 3];
            testText($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("对于文本框只能有一个值", $e->getMessage());
        }

        //测试单选值
        function testRadio($goodsController, $values, $service)
        {
            $goodsController->validateConstraint(1, DpGoodsConstraints::SPEC_CONSTRAINT, $values, $service);
        }

        $values = ["红"];
        testRadio($this->goodsController, $values, $service);

        //多值
        try {
            $values = ["红", "黄"];
            testRadio($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("对于单选框有且只能有一个值", $e->getMessage());
        }
        //单选值不存在
        try {
            $values = ["金"];
            testRadio($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("该单选选项不存在", $e->getMessage());
        }

        //测试多选框
        function testCheckbox($goodsController, $values, $service)
        {
            $goodsController->validateConstraint(2, DpGoodsConstraints::TYPE_CONSTRAINT, $values, $service);
        }

        $values = ["黄", "紫", "绿"];
        testCheckbox($this->goodsController, $values, $service);

        //多选值不存在
        try {
            $values = ["黄", "紫", "金"];
            testCheckbox($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("金不在多选选项中", $e->getMessage());
        }

        //多选值一项都没选
        try {
            $values = [];
            testCheckbox($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("多选框至少得选择一项", $e->getMessage());
        }

        //测试XY
        function testXY($goodsController, $values, $service)
        {
            $goodsController->validateConstraint(2, DpGoodsConstraints::SPEC_CONSTRAINT, $values, $service);
        }

        $values = [3, 6];
        testXY($this->goodsController, $values, $service);

        //X值不符合规范
        try {
            $values = [3.5, 6];
            testXY($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("类型约束值必须为整数", $e->getMessage());
        }
        //Y值不符合规范
        try {
            $values = [3, 6.5];
            testXY($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("类型约束值必须为整数", $e->getMessage());
        }
        //缺值
        try {
            $values = [3];
            testXY($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("XY型的值必须为两个", $e->getMessage());
        }

        //多值
        try {
            $values = [3, 4, 5, 6, 7];
            testXY($this->goodsController, $values, $service);
        } catch (\Exception $e) {
            $this->assertInstanceOf(AppException::class, $e);
            $this->assertEquals("XY型的值必须为两个", $e->getMessage());
        }
    }
}