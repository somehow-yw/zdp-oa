<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 12/12/16
 * Time: 5:02 PM
 */

namespace App\Providers;

use App\Repositories\Brands\BrandsHouseRepository;
use App\Repositories\Brands\Contracts\BrandsHouseRepository as BrandsHouseInterface;
use App\Repositories\Goods\Contracts\NewGoodsRepository as NewGoodsRepositoryInterface;
use App\Repositories\Goods\Contracts\RecommendGoodsRepository as RecommendGoodsRepositoryInterface;
use App\Repositories\Goods\NewGoodsRepository;
use App\Repositories\Goods\RecommendGoodsRepository;
use App\Repositories\Shops\Contracts\HighQualitySupplierRepository as HighQualitySupplierRepositoryInterface;
use App\Repositories\Shops\HighQualitySupplierRepository;
use Illuminate\Support\ServiceProvider;

/**
 * 首页管理服务提供者
 *
 * Class IndexServiceProvider
 * @package App\Providers
 */
class IndexManageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        //推荐商品仓库绑定
        $this->app->singleton(
            RecommendGoodsRepositoryInterface::class,
            RecommendGoodsRepository::class
        );
        //优质供应商仓库绑定
        $this->app->singleton(
            HighQualitySupplierRepositoryInterface::class,
            HighQualitySupplierRepository::class
        );
        //新上好货仓库绑定
        $this->app->singleton(
            NewGoodsRepositoryInterface::class,
            NewGoodsRepository::class
        );
        //品牌馆仓库绑定
        $this->app->singleton(
            BrandsHouseInterface::class,
            BrandsHouseRepository::class
        );
    }

    public function provides()
    {
        return [
            RecommendGoodsRepositoryInterface::class,
            HighQualitySupplierRepositoryInterface::class,
            NewGoodsRepositoryInterface::class,
            BrandsHouseInterface::class,
        ];
    }
}