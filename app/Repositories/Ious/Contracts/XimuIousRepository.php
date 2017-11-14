<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2017/8/16
 * Time: 15:25
 */

namespace App\Repositories\Ious\Contracts;


interface XimuIousRepository
{
    /**
     * 获取徙木冻品贷白名单
     *
     * @param       $size     integer 每页获取数量
     * @param array $queryArr 获取数据的条件
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($size, $queryArr);
}
