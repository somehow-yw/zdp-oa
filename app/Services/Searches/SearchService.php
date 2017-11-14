<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 2016/12/1
 * Time: 09:39
 */

namespace App\Services\Searches;


use App\Exceptions\AppException;
use App\Utils\AliCloudOss\OssAliossChatUtil;
use App\Utils\AliCloudOss\OssResponseCore;
use Storage;

class SearchService
{
    protected $requestUtil;
    protected $ossAliossChatUtil;
    protected $bucket;

    public function __construct(OssAliossChatUtil $ossAliossChatUtil)
    {
        $this->ossAliossChatUtil = $ossAliossChatUtil;
        $this->bucket = config('oss.bucket');
    }

    /**
     * @param $synonym
     *
     * @throws AppException
     */
    public function updateSearchSynonym($synonym)
    {
        $synonym = $this->parseAndUniqueSynonym($synonym);
        $fileName = 'synonym.txt';
        $file = $this->storeFileToLocal($fileName, $synonym);
        $object = $this->getOssSearchObjectPath($fileName);
        $this->uploadFileToOss($file, $object);
    }

    /**
     * @param $dict
     */
    public function updateCustomDict($dict)
    {
        $dict = $this->parseAndUniqueDict($dict);
        $fileName = 'custom.txt';
        $file = $this->storeFileToLocal($fileName, $dict);
        $object = $this->getOssDictObjectPath($fileName);
        $this->uploadFileToOss($file, $object);
    }

    /**
     * 解析并去重同义词
     *
     * @param $synonym
     *
     * 每一行是一个同义词组，每组同义词按逗号分割
     * ******************
     * 脑壳,猪头
     * 脑壳皮,猪头皮
     * 猪,养,吗
     * 号,吃,很
     * *******************
     *
     * @return string
     */
    protected function parseAndUniqueSynonym($synonym)
    {
        //替换中文逗号
        $synonym = str_replace("，", ",", $synonym);
        //按行分割
        $synonymArr = explode(PHP_EOL, $synonym);
        $dictValues = [];
        foreach ($synonymArr as $synonymGroup) {
            // 逗号分割并去空格
            $synonymItems = preg_split('/,/', trim($synonymGroup));
            // 去重
            $synonymItems = array_unique($synonymItems);
            // 排序
            asort($synonymItems);
            // 数组按逗号分割组成字符串
            $synonymItems = array_filter($synonymItems, 'strlen');
            $dictValues[] = implode(",", $synonymItems);
        }
        //同义词字符串
        $synonymStr = "";
        //去重同义词组
        $synonymArr = array_unique($dictValues);
        foreach ($synonymArr as $synonymItem) {
            //一行一组
            $synonymStr .= $synonymItem . PHP_EOL;
        }

        return $synonymStr;
    }

    /**
     * 解析并去重自定义字典
     *
     * @param $dict
     *
     * 自定义字典一行一项
     *
     * *************
     * 中国
     * 美国
     * 新加坡
     * 澳大利亚
     * *************
     *
     * @return string
     */
    protected function parseAndUniqueDict($dict)
    {
        $dictArr = explode(PHP_EOL, $dict);
        $dictValues = [];
        foreach ($dictArr as $dictItem) {
            $dictValues[] = trim($dictItem);
        }

        return implode(PHP_EOL, array_unique($dictValues));
    }

    /**
     * @param $fileName     string file name
     * @param $fileContent  string file content
     *
     * @return string file real path
     * @throws AppException
     */
    protected function storeFileToLocal($fileName, $fileContent)
    {
        $storeSuccess = Storage::disk('local')->put($fileName, $fileContent);
        if (!$storeSuccess) {
            throw  new   AppException("本地存储词典失败");
        }

        return storage_path('app' . '/' . $fileName);
    }

    /**
     * @param $fileName
     *
     * @return string
     */
    protected function getOssDictObjectPath($fileName)
    {
        return $object = config('oss.oss_object_path') . '/search/dict/' . $fileName;
    }

    protected function getOssSearchObjectPath($fileName)
    {
        return $object = config('oss.oss_object_path') . '/search/' . $fileName;
    }

    /**
     * 上传文件到OSS
     *
     * @param $file
     * @param $object
     *
     * @return array
     */
    protected function uploadFileToOss($file, $object)
    {
        $response = $this->ossAliossChatUtil->upload_file_by_file(
            $this->bucket,
            $object,
            $file,
            config('oss.options')
        );
        $this->assertResponse($response);
    }

    /**
     * @param $response
     *
     * @throws AppException
     */
    protected function assertResponse($response)
    {
        if (is_array($response) && isset($response['message'])
            && ($response['message'] instanceof OssResponseCore)
        ) {
            /** @var OssResponseCore $responseCore */
            $responseCore = $response['message'];
            if (!$responseCore->isOK()) {
                throw  new  AppException("oss上传失败" . $responseCore->body);
            }
        } else {
            throw  new AppException("oss上传失败");
        }
    }
}