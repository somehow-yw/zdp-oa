<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 9/27/16
 * Time: 9:13 PM
 */

namespace intg\App\Http\Controllers;

use intg\App\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MainDBBasedTestCase extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;

    function createApplication()
    {
        //设置当前数据库连接,以便测试数据库回滚
        putenv('DB_CONNECTION=mysql_zdp_main');

        return parent::createApplication();
    }
}