<?php
/**
 * Created by PhpStorm.
 * User: Chen
 * Date: 2017/9/1
 * Time: 9:38
 */
namespace App\Repositories\Tickl\Contracts;

interface TicklRepositories{
    /**
     * 获得所有商家消息反馈
     * @param $type
     * @param $pageSize
     * @param $pageNum
     * @return mixed
     */
    public function GetTicking($type,$pageSize,$pageNum);
}