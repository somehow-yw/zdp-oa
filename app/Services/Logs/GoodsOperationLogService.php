<?php
namespace App\Services\Logs;

use App\Exceptions\AppException;
use App\Exceptions\Events\AddGoodsOperationLogsException;
use App\Exceptions\Events\AddGoodsSnapshotException;
use App\Models\DpGoodsInfo;
use App\Models\DpGoodsOperationLog;
use App\Models\User;
use App\Utils\HTTPRequestUtil;
use App\Utils\RequestDataEncapsulationUtil;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 10/26/16
 * Time: 3:16 PM
 */

/**
 * Class GoodsOperationLogService
 *
 * @package App\Services\Logs
 * @var HTTPRequestUtil $httpClient http请求客户端
 * @var string          $host       日志服务器主机
 * @var string          $signKey    签名密钥
 */
class GoodsOperationLogService
{
    protected $httpClient;
    protected $host;
    protected $signKey;


    public function __construct(HTTPRequestUtil $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->host = config('request_url.log_request_url') . '/api/logs/goods';
        $this->signKey = config('signature.log_sign_key');
    }

    /**
     * 添加商品操作日志
     *
     * @param $goods             DpGoodsInfo 被操作的商品
     * @param $operateType       integer 操作类型
     * @param $operatorIdentity  integer 操作者身份
     * @param $operator          User    操作者
     * @param $formerAuditStatus integer 商品操作前的审核状态
     * @param $note              string  备注
     *
     * @return string
     * @throws AppException
     */
    public function addOperationLog($goods, $operateType, $operatorIdentity, $operator, $formerAuditStatus, $note)
    {
        // 请求头
        $headers = [
            'Accept' => 'application/json',
        ];

        // 请求体
        $requestBody = [
            'goods_id'            => $goods->id,
            'shop_id'             => $goods->shopid,
            'goods_name'          => $goods->gname,
            'type'                => DpGoodsOperationLog::$operationType[$operateType],
            'identity'            => $operatorIdentity,
            'user_id'             => $operator->id,
            'user_name'           => $operator->user_name,
            'former_audit_status' => $formerAuditStatus,
            'created_at'          => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        if (!is_null($note)) {
            $requestBody['note'] = $note;
        }
        $signedRequestBody = RequestDataEncapsulationUtil::getHttpRequestSign($requestBody, $this->signKey);
        //请求
        $response = $this->httpClient->post($this->getOperationAddUrl(), $signedRequestBody, $headers);

        if ($this->assertResponseOk($response)) {
            echo "添加日志成功" . PHP_EOL;
        } else {
            $request = json_encode($signedRequestBody);
            throw  new AppException(
                "请求日志服务器添加操作日志失败{$request}",
                AddGoodsOperationLogsException::ADD_OPERATION_LOG_FAILED
            );
        }
        $responseArr = json_decode($response, true);

        return $responseArr['data']['log_id'];
    }

    /**
     * 创建快照
     *
     * @param $goodsId integer 商品id
     * @param $logId   string 商品操作日志hashID
     *
     * @throws AppException
     */
    public function makeSnapshot($goodsId, $logId)
    {
        $goodsSnapshot = DpGoodsInfo::with(
            'goodsAttribute',
            'specialAttribute',
            'goodsPicture',
            'goodsInspectionReport'
        )->where('dp_goods_info.id', $goodsId)
            ->first()->toArray();

        // 请求头
        $headers = [
            'Accept' => 'application/json',
        ];

        $requestData = [
            '_id'        => $logId,
            'goods_info' => $goodsSnapshot,
            'goods_id'   => $goodsId,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $requestBody = ['data' => json_encode($requestData)];

        $signedRequestBody = RequestDataEncapsulationUtil::getHttpRequestSign($requestBody, $this->signKey);

        $response = $this->httpClient->post($this->getSnapshotAddUrl(), $signedRequestBody, $headers);

        if ($this->assertResponseOk($response)) {
            echo "添加快照成功" . PHP_EOL;
        } else {
            throw new AppException("请求日志服务器添加快照失败:{$response}", AddGoodsSnapshotException::ADD_GOODS_SNAPSHOT_FAILED);
        }
    }

    /**
     * 获取添加操作日志url
     *
     * @return string
     */
    private function getOperationAddUrl()
    {
        return $this->host . '/operate/add';
    }

    /**
     * 获取添加快照url
     *
     * @return string
     */
    private function getSnapshotAddUrl()
    {
        return $this->host . '/snapshot/add';
    }

    /**
     * 断言请求是否成功
     *
     * @param  $response string
     *
     * @return boolean
     */
    private function assertResponseOk($response)
    {
        $response = json_decode($response, true);
        if (0 === json_last_error() && is_array($response) && array_has($response, 'code') && 0 === $response['code']) {
            return true;
        } else {
            return false;
        }
    }
}