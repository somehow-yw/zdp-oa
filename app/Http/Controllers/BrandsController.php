<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BrandsService;

class BrandsController extends Controller
{
    /**
     * 获取品牌列表
     *
     * @param Request       $request
     * @param BrandsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function getBrandsList(Request $request, BrandsService $service)
    {
        $this->validate(
            $request,
            [
                'size'  => 'required|integer|between:1,5000',
                'page'  => 'required|integer|between:1,99999',
                'brand' => 'string|max:20',
            ],
            [
                'page.required' => '当前页数必须有',
                'page.integer'  => '当前页数必须是一个整型',
                'page.between'  => '当前页数必须是:min, 到:max的整数',

                'size.required' => '获取数量必须有',
                'size.integer'  => '获取数量必须是一个整型',
                'size.between'  => '获取数量必须是:min, 到:max的整数',

                'brand.string' => '品牌名必须为字符串',
                'brand.max'    => '品牌名不能超过:max个字符',
            ]
        );
        $reData =
            $service->getBrandsList(
                $request->input('size'),
                $request->input('page'),
                $request->input('brand', null)
            );

        return $this->render(
            'brands.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 添加品牌
     *
     * @param Request       $request
     * @param BrandsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function createBrand(Request $request, BrandsService $service)
    {
        $this->validate(
            $request,
            [
                'brand'     => 'required|string|max:20',
                'key_words' => 'required|string|max:40',
            ],
            [
                'brand.required' => '品牌名不能为空',
                'brand.string'   => '品牌名必须为字符串',
                'brand.max'      => '品牌名不能超过:max个字符',

                'key_words.required' => '关键字不能为空',
                'key_words.string'   => '关键字必须为字符串',
                'key_words.max'      => '关键字不能超过:max个字符',
            ]
        );
        $service->createBrand($request->input('brand'), $request->input('key_words'));
        $reData = [
            'code'    => 0,
            'data'    => [],
            'message' => "OK",
        ];

        return $this->render(
            'brands.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 更新品牌
     *
     * @param Request       $request
     * @param BrandsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function updateBrand(Request $request, BrandsService $service)
    {
        $this->validate(
            $request,
            [
                'id'        => 'required|integer',
                'brand'     => 'required|string|max:20',
                'key_words' => 'required|string|max:40',
            ],
            [
                'id.required' => '品牌id不能为空',
                'id.integer'  => '品牌id必须为整形',

                'brand.required' => '品牌名不能为空',
                'brand.string'   => '品牌名必须为字符串',
                'brand.max'      => '品牌名不能超过:max个字符',

                'key_words.required' => '关键字不能为空',
                'key_words.string'   => '关键字必须为字符串',
                'key_words.max'      => '关键字不能超过:max个字符',
            ]
        );

        $service->updateBrand($request->input('id'), $request->input('brand'), $request->input('key_words'));
        $reData = [
            'code'    => 0,
            'data'    => [],
            'message' => "OK",
        ];

        return $this->render(
            'brands.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }

    /**
     * 删除品牌
     *
     * @param Request       $request
     * @param BrandsService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteBrand(Request $request, BrandsService $service)
    {
        $this->validate(
            $request,
            [
                'id' => 'required|integer',
            ],
            [
                'id.required' => '品牌id不能为空',
                'id.integer'  => 'id必须是一个整形',
            ]
        );
        $service->deleteBrand($request->input('id'));

        $reData = [
            'code'    => 0,
            'data'    => [],
            'message' => "OK",
        ];

        return $this->render(
            'brands.list',
            $reData['data'],
            $reData['message'],
            $reData['code']
        );
    }
}
