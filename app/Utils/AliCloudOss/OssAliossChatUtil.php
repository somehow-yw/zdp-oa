<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/11/15
 * Time: 18:41
 */

namespace App\Utils\AliCloudOss;

use App\Utils\AliCloudOss\OssRequestCore;
use App\Exceptions\Oss\OssException;
use App\Utils\AliCloudOss\OssResponseCore;

/**
 * Class aliossChatUtil.
 * OSS相应操作
 * @package App\Utils
 */
class OssAliossChatUtil
{
    /** OSS 内部常量 */
    const NAME    = 'aliyun-oss-sdk-php';
    const BUILD   = '20150311';
    const VERSION = '1.1.7';
    const AUTHOR  = 'idongpin';

    const OSS_BUCKET                 = 'bucket';
    const OSS_OBJECT                 = 'object';
    const OSS_HEADERS                = 'headers';
    const OSS_METHOD                 = 'method';
    const OSS_QUERY                  = 'query';
    const OSS_BASENAME               = 'basename';
    const OSS_MAX_KEYS               = 'max-keys';
    const OSS_UPLOAD_ID              = 'uploadId';
    const OSS_PART_NUM               = 'partNumber';
    const OSS_MAX_KEYS_VALUE         = 100;
    const OSS_MAX_OBJECT_GROUP_VALUE = 1000;
    const OSS_MAX_PART_SIZE          = 524288000;
    const OSS_MID_PART_SIZE          = 52428800;
    const OSS_MIN_PART_SIZE          = 5242880;
    const OSS_FILE_SLICE_SIZE        = 8192;
    const OSS_PREFIX                 = 'prefix';
    const OSS_DELIMITER              = 'delimiter';
    const OSS_MARKER                 = 'marker';
    const OSS_CONTENT_MD5            = 'Content-Md5';
    const OSS_SELF_CONTENT_MD5       = 'x-oss-meta-md5';
    const OSS_CONTENT_TYPE           = 'Content-Type';
    const OSS_CONTENT_LENGTH         = 'Content-Length';
    const OSS_IF_MODIFIED_SINCE      = 'If-Modified-Since';
    const OSS_IF_UNMODIFIED_SINCE    = 'If-Unmodified-Since';
    const OSS_IF_MATCH               = 'If-Match';
    const OSS_IF_NONE_MATCH          = 'If-None-Match';
    const OSS_CACHE_CONTROL          = 'Cache-Control';
    const OSS_EXPIRES                = 'Expires';
    const OSS_PREAUTH                = 'preauth';
    const OSS_CONTENT_COING          = 'Content-Coding';
    const OSS_CONTENT_DISPOSTION     = 'Content-Disposition';
    const OSS_RANGE                  = 'range';
    const OSS_ETAG                   = 'etag';
    const OSS_LAST_MODIFIED          = 'lastmodified';
    const OS_CONTENT_RANGE           = 'Content-Range';
    const OSS_CONTENT                = 'content';
    const OSS_BODY                   = 'body';
    const OSS_LENGTH                 = 'length';
    const OSS_HOST                   = 'Host';
    const OSS_DATE                   = 'Date';
    const OSS_AUTHORIZATION          = 'Authorization';
    const OSS_FILE_DOWNLOAD          = 'fileDownload';
    const OSS_FILE_UPLOAD            = 'fileUpload';
    const OSS_PART_SIZE              = 'partSize';
    const OSS_SEEK_TO                = 'seekTo';
    const OSS_SIZE                   = 'size';
    const OSS_QUERY_STRING           = 'query_string';
    const OSS_SUB_RESOURCE           = 'sub_resource';
    const OSS_DEFAULT_PREFIX         = 'x-oss-';
    const OSS_CHECK_MD5              = 'checkmd5';
    /*%******************************************************************************************%*/
    // 私有URL变量
    const OSS_URL_ACCESS_KEY_ID = 'OSSAccessKeyId';
    const OSS_URL_EXPIRES       = 'Expires';
    const OSS_URL_SIGNATURE     = 'Signature';
    /*%******************************************************************************************%*/
    // HTTP方法
    const OSS_HTTP_GET     = 'GET';
    const OSS_HTTP_PUT     = 'PUT';
    const OSS_HTTP_HEAD    = 'HEAD';
    const OSS_HTTP_POST    = 'POST';
    const OSS_HTTP_DELETE  = 'DELETE';
    const OSS_HTTP_OPTIONS = 'OPTIONS';
    /*%******************************************************************************************%*/
    // 其他常量
    const OSS_ACL                      = 'x-oss-acl';
    const OSS_OBJECT_GROUP             = 'x-oss-file-group';
    const OSS_MULTI_PART               = 'uploads';
    const OSS_MULTI_DELETE             = 'delete';
    const OSS_OBJECT_COPY_SOURCE       = 'x-oss-copy-source';
    const OSS_OBJECT_COPY_SOURCE_RANGE = "x-oss-copy-source-range";
    // 支持STS SecurityToken
    const OSS_SECURITY_TOKEN = "x-oss-security-token";

    const OSS_ACL_TYPE_PRIVATE           = 'private';
    const OSS_ACL_TYPE_PUBLIC_READ       = 'public-read';
    const OSS_ACL_TYPE_PUBLIC_READ_WRITE = 'public-read-write';
    // OSS ACL数组
    static $OSS_ACL_TYPES = [
        self::OSS_ACL_TYPE_PRIVATE,
        self::OSS_ACL_TYPE_PUBLIC_READ,
        self::OSS_ACL_TYPE_PUBLIC_READ_WRITE,
    ];

    // CORS 相关
    const OSS_CORS_ALLOWED_ORIGIN     = 'AllowedOrigin';
    const OSS_CORS_ALLOWED_METHOD     = 'AllowedMethod';
    const OSS_CORS_ALLOWED_HEADER     = 'AllowedHeader';
    const OSS_CORS_EXPOSE_HEADER      = 'ExposeHeader';
    const OSS_CORS_MAX_AGE_SECONDS    = 'MaxAgeSeconds';
    const OSS_OPTIONS_ORIGIN          = 'Origin';
    const OSS_OPTIONS_REQUEST_METHOD  = 'Access-Control-Request-Method';
    const OSS_OPTIONS_REQUEST_HEADERS = 'Access-Control-Request-Headers';

    /** 类的私有属性 */
    public    $version = self::VERSION;  //接口板本
    protected $use_ssl = false;  //是否使用SSL
    //是否使用debug模式
    private $debug_mode  = false;  //是否开启DEBUG模式
    private $max_retries = 3;
    private $redirects   = 0;
    private $vhost;
    //路径表现方式
    private $enable_domain_style = false;
    private $request_url;  //请求地址
    private $access_id;  //Access Key ID
    private $access_key;  //Access Key Secret
    private $hostname;  //主机名
    private $port;  //端口
    private $security_token;
    private $enable_sts_in_url   = false;
    //oss默认响应头
    private $OSS_DEFAULT_REAPONSE_HEADERS = [
        'date', 'content-type', 'content-length', 'connection', 'accept-ranges', 'cache-control', 'content-disposition',
        'content-encoding', 'content-language',
        'etag', 'expires', 'last-modified', 'server',
    ];
    private $ossRequestCore;
    private $ossResponseCore;

    /**
     * aliossChatUtil constructor.
     *
     * @param $ossRequestCore  OssRequestCore
     * @param $ossResponseCore OssResponseCore
     */

    public function __construct()
    {
        $this->access_id = config('oss.oss_cons_info.access_id');
        $this->access_key = config('oss.oss_cons_info.access_key');
        $this->hostname = config('oss.oss_cons_info.hostname');
        //支持sts的security token
        $this->security_token = config('oss.oss_cons_info.security_token');
    }

    /**
     * 上传文件，适合比较大的文件
     *
     * @param      $bucket
     * @param      $object
     * @param      $file
     * @param null $options
     *
     * @return ResponseCore|string
     * @throws OssException
     */
    public function upload_file_by_file($bucket, $object, $file, $options = null)
    {
        $dateTxt = date('Y-m-d H:i:s', time());
        $options['headers']['Expires'] = $dateTxt;
        $options['headers']['Cache-Control'] = $dateTxt;
        if (empty($bucket)) {
            throw new OssException(OssException::BUCKET_NULL);
        }
        if (empty($object)) {
            throw new OssException(OssException::OSS_OBJECT_NULL);
        }
        if ($options != null && !is_array($options)) {
            throw new OssException(OssException::OPTIONS_ERROR);
        } elseif (empty($options)) {
            $options = [];
        }
        if (empty($file)) {
            throw new OssException(OssException::FILE_NULL);
        }
        //进行转码处理，将中文转码为UTF-8
        $file = $this->encoding_path($file);
        $options[self::OSS_FILE_UPLOAD] = $file;  //给options加入文件路径属性
        if (!file_exists($options[self::OSS_FILE_UPLOAD])) {  //判断文件是否存在
            throw new OssException(OssException::FILE_NULL);
        }
        $file_size = filesize($options[self::OSS_FILE_UPLOAD]);  //取得文件的大小
        $is_check_md5 = $this->is_check_md5($options);  //取得文件的MD5值
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5_file($options[self::OSS_FILE_UPLOAD], true));
            $options[self::OSS_CONTENT_MD5] = $content_md5;  //Content-Md5
        }
        $content_type = $this->get_mimetype($file);
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;  //method=PUT
        $options[self::OSS_BUCKET] = $bucket;  //bucket
        $options[self::OSS_OBJECT] = $object;  //object
        $options[self::OSS_CONTENT_TYPE] = $content_type;  //Content-Type
        $options[self::OSS_CONTENT_LENGTH] = $file_size;  //Content-Length

        $response = $this->auth($options);

        return $response;
    }

    /**
     * 删除object
     *
     * @param string $bucket  (Required)
     * @param string $object  (Required)
     * @param array  $options (Optional)
     *
     * @author xiaobing
     * @since  2011-11-14
     * @return ResponseCore
     */
    public function delete_object($bucket, $object, $options = null)
    {
        $this->precheck_common($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = $object;

        return $this->auth($options);
    }

    /**
     * 批量删除objects
     * @throws OSS_Exception
     *
     * @param string $bucket  (Required)
     * @param array  $objects (Required)
     * @param array  $options (Optional)
     *
     * @author xiaobing
     * @since  2012-03-09
     * @return ResponseCore
     */
    public function delete_objects($bucket, $objects, $options = null)
    {
        $this->precheck_common($bucket, null, $options, false);
        //objects
        if (!is_array($objects) || !$objects) {
            throw new OssException([], '删除对象（objects）必须是一个数组');
        }

        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'delete';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Delete></Delete>');
        // Quiet mode
        if (isset($options['quiet'])) {
            $quiet = 'false';
            if (is_bool($options['quiet'])) { //Boolean
                $quiet = $options['quiet'] ? 'true' : 'false';
            } elseif (is_string($options['quiet'])) { // String
                $quiet = ($options['quiet'] === 'true') ? 'true' : 'false';
            }
            $xml->addChild('Quiet', $quiet);
        }
        // Add the objects
        foreach ($objects as $object) {
            $sub_object = $xml->addChild('Object');
            $search = ['<', '>', '&', '\'', '"'];
            $replace = ['&lt;', '&gt;', '&amp;', '&apos;', '&quot;'];
            $object = str_replace($search, $replace, $object);
            $sub_object->addChild('Key', $object);
        }
        $options[self::OSS_CONTENT] = $xml->asXML();

        return $this->auth($options);
    }

    /*%******************************************************************************************************%*/
    //请求
    /**
     * auth接口
     *
     * @param array $options
     *
     * @return ResponseCore
     * @throws OSS_Exception
     * @throws RequestCore_Exception
     */
    public function auth($options)
    {
        if ($options != null && !is_array($options)) {
            throw new OssException(OssException::OSS_OPTION_NOT_ARR);
        }

        //验证Bucket,list_bucket时不需要验证
        if (!(('/' == $options[self::OSS_OBJECT])
              && ('' == $options[self::OSS_BUCKET])
              && ('GET' == $options[self::OSS_METHOD]))
            && !$this->validate_bucket($options[self::OSS_BUCKET])
        ) {
            throw new OssException(OssException::OSS_BUCKET_ERROR);
        }
        //验证Object
        if (isset($options[self::OSS_OBJECT]) && !$this->validate_object($options[self::OSS_OBJECT])) {
            throw new OssException(OssException::OSS_OBJECT_ERROR);
        }
        //Object编码为UTF-8
        $tmp_object = $options[self::OSS_OBJECT];
        try {
            if ($this->is_gb2312($options[self::OSS_OBJECT])) {
                $options[self::OSS_OBJECT] = iconv('GB2312', "UTF-8//IGNORE", $options[self::OSS_OBJECT]);
            } elseif ($this->check_char($options[self::OSS_OBJECT], true)) {
                $options[self::OSS_OBJECT] = iconv('GBK', "UTF-8//IGNORE", $options[self::OSS_OBJECT]);
            }
        } catch (\Exception $e) {
            try {
                $tmp_object = iconv(mb_detect_encoding($tmp_object), "UTF-8", $tmp_object);
                $options[self::OSS_OBJECT] = $tmp_object;
            } catch (\Exception $e) {
            }
        }

        //验证ACL
        if (isset($options[self::OSS_HEADERS][self::OSS_ACL]) &&
            !empty($options[self::OSS_HEADERS][self::OSS_ACL])
        ) {
            //headers x-oss-acl
            if (!in_array(strtolower($options[self::OSS_HEADERS][self::OSS_ACL]), self::$OSS_ACL_TYPES)) {
                throw new OssException(
                    [], 'ACL不在允许范围,目前仅允许(private,public-read,public-read-write三种权限)'
                );
            }
        }

        //定义scheme
        $scheme = $this->use_ssl ? 'https://' : 'http://';
        if ($this->enable_domain_style) {
            $hostname = $this->vhost
                ? $this->vhost
                : (($options[self::OSS_BUCKET] == '')
                    ? $this->hostname
                    : ($options[self::OSS_BUCKET] . '.') . $this->hostname);
        } else {
            $hostname = (isset($options[self::OSS_BUCKET]) && '' !== $options[self::OSS_BUCKET])
                ? $this->hostname . '/' . $options[self::OSS_BUCKET]
                : $this->hostname;
        }

        //请求参数
        $signable_resource = '';
        $query_string_params = [];
        $signable_query_string_params = [];
        $string_to_sign = '';

        $oss_host = $this->hostname;
        if ($this->enable_domain_style) {
            $oss_host = $hostname;
        }
        $headers = [
            self::OSS_CONTENT_MD5  => '',
            self::OSS_CONTENT_TYPE => isset($options[self::OSS_CONTENT_TYPE])
                ? $options[self::OSS_CONTENT_TYPE]
                : 'application/x-www-form-urlencoded',
            self::OSS_DATE         => isset($options[self::OSS_DATE])
                ? $options[self::OSS_DATE]
                : gmdate('D, d M Y H:i:s \G\M\T'),
            self::OSS_HOST         => $oss_host,
        ];

        if (isset($options[self::OSS_CONTENT_MD5])) {
            $headers[self::OSS_CONTENT_MD5] = $options[self::OSS_CONTENT_MD5];
        }

        //增加stsSecurityToken
        if ((!is_null($this->security_token)) && (!$this->enable_sts_in_url)) {
            $headers[self::OSS_SECURITY_TOKEN] = $this->security_token;
        }

        if (isset($options[self::OSS_OBJECT]) && '/' !== $options[self::OSS_OBJECT]) {
            $signable_resource = '/' . str_replace(['%2F', '%25'], ['/', '%'],
                    rawurlencode($options[self::OSS_OBJECT])
                );
        }

        if (isset($options[self::OSS_QUERY_STRING])) {  //query_string
            $query_string_params = array_merge($query_string_params, $options[self::OSS_QUERY_STRING]);
        }
        $query_string = $this->to_query_string($query_string_params);
        $signable_list = [
            self::OSS_PART_NUM,
            'response-content-type',
            'response-content-language',
            'response-cache-control',
            'response-content-encoding',
            'response-expires',
            'response-content-disposition',
            self::OSS_UPLOAD_ID,
        ];

        foreach ($signable_list as $item) {
            if (isset($options[$item])) {
                $signable_query_string_params[$item] = $options[$item];
            }
        }

        if ($this->enable_sts_in_url && (!is_null($this->security_token))) {
            $signable_query_string_params["security-token"] = $this->security_token;
        }
        $signable_query_string = $this->to_query_string($signable_query_string_params);

        //合并 HTTP headers
        if (isset($options[self::OSS_HEADERS])) {
            $headers = array_merge($headers, $options[self::OSS_HEADERS]);
        }

        //生成请求URL
        $conjunction = '?';
        $non_signable_resource = '';
        if (isset($options[self::OSS_SUB_RESOURCE])) {
            $signable_resource .= $conjunction . $options[self::OSS_SUB_RESOURCE];
            $conjunction = '&';
        }
        if ($signable_query_string !== '') {
            $signable_query_string = $conjunction . $signable_query_string;
            $conjunction = '&';
        }
        if ($query_string !== '') {
            $non_signable_resource .= $conjunction . $query_string;
            $conjunction = '&';
        }
        $this->request_url = $scheme . $hostname . $signable_resource . $signable_query_string . $non_signable_resource;

        //创建请求
        $this->ossRequestCore = new OssRequestCore();
        $this->ossRequestCore->setRequestClass();
        $this->ossRequestCore->setUrl($this->request_url);
        $user_agent = self::NAME . "/" . self::VERSION .
                      " (" . php_uname('s') . "/" . php_uname('r') . "/" . php_uname('m') . ";" . PHP_VERSION . ")";
        $this->ossRequestCore->set_useragent($user_agent);

        // Streaming uploads
        if (isset($options[self::OSS_FILE_UPLOAD])) {
            if (is_resource($options[self::OSS_FILE_UPLOAD])) {
                $length = null;
                if (isset($options[self::OSS_CONTENT_LENGTH])) {
                    $length = $options[self::OSS_CONTENT_LENGTH];
                } elseif (isset($options[self::OSS_SEEK_TO])) {
                    $stats = fstat($options[self::OSS_FILE_UPLOAD]);
                    if ($stats && $stats[self::OSS_SIZE] >= 0) {
                        $length = $stats[self::OSS_SIZE] - (integer)$options[self::OSS_SEEK_TO];
                    }
                }
                $this->ossRequestCore->set_read_stream($options[self::OSS_FILE_UPLOAD], $length);
                if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
                    $headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
                }
            } else {
                $this->ossRequestCore->set_read_file($options[self::OSS_FILE_UPLOAD]);
                $length = $this->ossRequestCore->read_stream_size;
                if (isset($options[self::OSS_CONTENT_LENGTH])) {
                    $length = $options[self::OSS_CONTENT_LENGTH];
                } elseif (isset($options[self::OSS_SEEK_TO]) && isset($length)) {
                    $length -= (integer)$options[self::OSS_SEEK_TO];
                }
                $this->ossRequestCore->set_read_stream_size($length);
                if (isset($headers[self::OSS_CONTENT_TYPE]) &&
                    ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded')
                ) {
                    $mime_type = self::get_mime_type($options[self::OSS_FILE_UPLOAD]);
                    $headers[self::OSS_CONTENT_TYPE] = $mime_type;
                }
            }
        }

        if (isset($options[self::OSS_SEEK_TO])) {
            $this->ossRequestCore->set_seek_position((integer)$options[self::OSS_SEEK_TO]);
        }

        if (isset($options[self::OSS_FILE_DOWNLOAD])) {
            if (is_resource($options[self::OSS_FILE_DOWNLOAD])) {
                $this->ossRequestCore->set_write_stream($options[self::OSS_FILE_DOWNLOAD]);
            } else {
                $this->ossRequestCore->set_write_file($options[self::OSS_FILE_DOWNLOAD]);
            }
        }

        if (isset($options[self::OSS_METHOD])) {
            $this->ossRequestCore->set_method($options[self::OSS_METHOD]);
            $string_to_sign .= $options[self::OSS_METHOD] . "\n";
        }

        if (isset($options[self::OSS_CONTENT])) {
            $this->ossRequestCore->set_body($options[self::OSS_CONTENT]);
            if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
                $headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
            }
            $headers[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
            $headers[self::OSS_CONTENT_MD5] = base64_encode(md5($options[self::OSS_CONTENT], true));
        }
        uksort($headers, 'strnatcasecmp');
        foreach ($headers as $header_key => $header_value) {
            $header_value = str_replace(["\r", "\n"], '', $header_value);
            if ($header_value !== '') {
                $this->ossRequestCore->add_header($header_key, $header_value);
            }
            if (
                strtolower($header_key) === 'content-md5' ||
                strtolower($header_key) === 'content-type' ||
                strtolower($header_key) === 'date' ||
                (isset($options[self::OSS_PREAUTH]) && (integer)$options[self::OSS_PREAUTH] > 0)
            ) {
                $string_to_sign .= $header_value . "\n";
            } elseif (substr(strtolower($header_key), 0, 6) === self::OSS_DEFAULT_PREFIX) {
                $string_to_sign .= strtolower($header_key) . ':' . $header_value . "\n";
            }
        }

        $string_to_sign .= '/' . $options[self::OSS_BUCKET];
        $string_to_sign .= $this->enable_domain_style
            ? ($options[self::OSS_BUCKET] != ''
                ? ($options[self::OSS_OBJECT] == '/' ? '/' : '') : '')
            : '';
        $string_to_sign .= rawurldecode($signable_resource) . urldecode($signable_query_string);
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->access_key, true));
        $this->ossRequestCore->add_header('Authorization', 'OSS ' . $this->access_id . ':' . $signature);

        if (isset($options[self::OSS_PREAUTH]) && (integer)$options[self::OSS_PREAUTH] > 0) {
            $signed_url =
                $this->request_url . $conjunction . self::OSS_URL_ACCESS_KEY_ID . '=' . rawurlencode($this->access_id) .
                '&' . self::OSS_URL_EXPIRES . '=' . $options[self::OSS_PREAUTH] . '&' . self::OSS_URL_SIGNATURE . '=' .
                rawurlencode($signature);

            return ['status' => '1', 'message' => $signed_url];
        } elseif (isset($options[self::OSS_PREAUTH])) {
            return ['status' => '1', 'message' => $this->request_url];
        }

        if ($this->debug_mode) {
            $this->ossRequestCore->debug_mode = $this->debug_mode;
        }

        $this->ossRequestCore->send_request();

        $response_header = $this->ossRequestCore->get_response_header();
        $response_header['oss-request-url'] = $this->request_url;
        $response_header['oss-redirects'] = $this->redirects;
        $response_header['oss-stringtosign'] = $string_to_sign;
        $response_header['oss-requestheaders'] = $this->ossRequestCore->request_headers;

        $this->ossResponseCore = new OssResponseCore();
        $responseData = $this->ossResponseCore->setHeader($response_header)
            ->setBody($this->ossRequestCore->get_response_body())
            ->setStatus($this->ossRequestCore->get_response_code());

        //如果OSS内部错误，进行重试
        if ((integer)$this->ossRequestCore->get_response_code() === 500) {
            if ($this->redirects <= $this->max_retries) {
                //设置休眠
                $delay = (integer)(pow(4, $this->redirects) * 100000);
                usleep($delay);
                $this->redirects++;
                $this->auth($options);
            }
        }
        $this->redirects = 0;

        return ['code' => '0', 'message' => $responseData];
    }

    /**
     * 生成query params
     *
     * @param array $array 关联数组
     *
     * @return string 返回诸如 key1=value1&key2=value2
     */
    public function to_query_string($options = [])
    {
        $temp = [];
        uksort($options, 'strnatcasecmp');
        foreach ($options as $key => $value) {
            if (is_string($key) && !is_array($value)) {
                $temp[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }

        return implode('&', $temp);
    }

    /*%******************************************************************************************************%*/
    //带签名的url相关

    /**
     * 获取GET签名的url
     *
     * @param string $bucket  (Required)
     * @param string $object  (Required)
     * @param int    $timeout (Optional)
     * @param array  $options (Optional)
     *
     * @author xiaobing
     * @since  2011-12-21
     * @return string
     */
    public function get_sign_url($bucket, $object, $timeout = 60, $options = null)
    {
        return $this->presign_url($bucket, $object, $timeout, self::OSS_HTTP_GET, $options);
    }

    /**
     * 获取签名url,支持生成get和put签名
     *
     * @param string $bucket
     * @param string $object
     * @param int    $timeout
     * @param array  $options (Optional) Key-Value数组
     * @param string $method
     *
     * @return ResponseCore
     * @throws OSS_Exception
     */
    public function presign_url($bucket, $object, $timeout = 60, $method = self::OSS_HTTP_GET, $options = null)
    {
        $this->precheck_common($bucket, $object, $options);
        //method
        if (self::OSS_HTTP_GET !== $method && self::OSS_HTTP_PUT !== $method) {
            throw new OssException([], '请求方式错误');
        }
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_METHOD] = $method;
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = '';
        }
        $timeout = time() + $timeout;
        $options[self::OSS_PREAUTH] = $timeout;
        $options[self::OSS_DATE] = $timeout;

        //$this->set_sign_sts_in_url(true);

        return $this->auth($options);
    }

    /**
     * 检测options参数
     *
     * @param array $options
     *
     * @throws OSS_Exception
     */
    private function precheck_options(&$options)
    {
        if ($options != null && !is_array($options)) {
            throw new OssException([], 'options参数错误');
        }
        if (!$options) {
            $options = [];
        }
    }

    /**
     * 校验bucket参数
     *
     * @param string $bucket
     * @param string $err_msg
     *
     * @throws OSS_Exception
     */
    private function precheck_bucket($bucket, $err_msg = '')
    {
        if (empty($bucket)) {
            if (empty($err_msg)) {
                $err_msg = 'bucket错误';
            }
            throw new OssException([], $err_msg);
        }
    }

    /**
     * 校验object参数
     *
     * @param string $object
     *
     * @throws OSS_Exception
     */
    private function precheck_object($object)
    {
        if (empty($object)) {
            throw new OssException([], 'object参数错误');
        }
    }

    /**
     * 校验bucket,options参数
     *
     * @param string $bucket
     * @param string $object
     * @param array  $options
     * @param bool   $is_check_object
     */
    private function precheck_common($bucket, $object, &$options, $is_check_object = true)
    {
        if ($is_check_object) {
            $this->precheck_object($object);
        }
        $this->precheck_options($options);
        $this->precheck_bucket($bucket);
    }

    /**
     * 参数校验
     *
     * @param array  $options
     * @param string $param
     * @param string $func_name
     *
     * @throws OSS_Exception
     */
    private function precheck_param($options, $param, $func_name)
    {
        if (!isset($options[$param])) {
            throw new OssException([], 'The `' . $param . '` options is required in ' . $func_name . '().');
        }
    }

    /**
     * 获取mimetype类型
     *
     * @param string $object
     *
     * @return string
     */
    private function get_mime_type($object)
    {
        $extension = explode('.', $object);
        $extension = array_pop($extension);
        $mime_type = $this->get_mimetype(strtolower($extension));

        return $mime_type;
    }

    /**
     * 检测是否GBK编码
     *
     * @param string  $str
     * @param boolean $gbk
     *
     * @author xiaobing
     * @since  2012-06-04
     * @return boolean
     */
    public function check_char($str, $gbk = true)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $v = ord($str[$i]);
            if ($v > 127) {
                if (($v >= 228) && ($v <= 233)) {
                    if (($i + 2) >= (strlen($str) - 1)) {
                        return $gbk ? true : false;
                    }  // not enough characters
                    $v1 = ord($str[$i + 1]);
                    $v2 = ord($str[$i + 2]);
                    if ($gbk) {
                        return (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) ? false : true;//GBK
                    } else {
                        return (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) ? true : false;
                    }
                }
            }
        }

        return $gbk ? true : false;
    }

    /**
     * 检测是否GB2312编码
     *
     * @param string $str
     *
     * @author xiaobing
     * @since  2012-03-20
     * @return boolean false UTF-8编码  TRUE GB2312编码
     */
    public function is_gb2312($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            $v = ord($str[$i]);
            if ($v > 127) {
                if (($v >= 228) && ($v <= 233)) {
                    if (($i + 2) >= (strlen($str) - 1)) {
                        return true;
                    }  // not enough characters
                    $v1 = ord($str[$i + 1]);
                    $v2 = ord($str[$i + 2]);
                    if (($v1 >= 128) && ($v1 <= 191) && ($v2 >= 128) && ($v2 <= 191)) {
                        return false;
                    }   //UTF-8编码
                    else {
                        return true;
                    }    //GB编码
                }
            }
        }
    }

    /**
     * 检验bucket名称是否合法
     * bucket的命名规范：
     * 1. 只能包括小写字母，数字
     * 2. 必须以小写字母或者数字开头
     * 3. 长度必须在3-63字节之间
     *
     * @param string $bucket (Required)
     *
     * @author xiaobing
     * @since  2011-12-27
     * @return boolean
     */
    public function validate_bucket($bucket)
    {
        $pattern = '/^[a-z0-9][a-z0-9-]{2,62}$/';
        if (!preg_match($pattern, $bucket)) {
            return false;
        }

        return true;
    }

    /**
     * 检验object名称是否合法
     * object命名规范:
     * 1. 规则长度必须在1-1023字节之间
     * 2. 使用UTF-8编码
     *
     * @param string $object (Required)
     *
     * @author xiaobing
     * @since  2011-12-27
     * @return boolean
     */
    public function validate_object($object)
    {
        $pattern = '/^.{1,1023}$/';
        if (empty($object) || !preg_match($pattern, $object)) {
            return false;
        }

        return true;
    }

    /**
     * 检测md5
     *
     * @param array $options
     *
     * @return bool|null
     */
    private function is_check_md5($options)
    {
        return $this->get_value($options, self::OSS_CHECK_MD5, false, true, true);
    }

    /**
     * 获取value
     *
     * @param array  $options
     * @param string $key
     * @param string $default
     * @param bool   $is_check_empty
     * @param bool   $is_check_bool
     *
     * @return bool|null
     */
    private function get_value($options, $key, $default = null, $is_check_empty = false, $is_check_bool = false)
    {
        $value = $default;
        if (isset($options[$key])) {
            if ($is_check_empty) {
                if (!empty($options[$key])) {
                    $value = $options[$key];
                }
            } else {
                $value = $options[$key];
            }
            unset($options[$key]);
        }
        if ($is_check_bool) {
            if ($value !== true && $value !== false) {
                $value = false;
            }
        }

        return $value;
    }

    /**
     * 主要是由于windows系统编码是gbk，遇到中文时候，如果不进行转换处理会出现找不到文件的问题
     *
     * @param $file_path
     *
     * @return string
     */
    public function encoding_path($file_path)
    {
        if ($this->chk_chinese($file_path)) {
            $file_path = iconv('utf-8', 'gbk', $file_path);
        }

        return $file_path;
    }

    /**
     * @param $str
     *
     * @return int
     */
    public function chk_chinese($str)
    {
        return preg_match('/[\x80-\xff]./', $str);
    }

    /**
     * 检测是否windows系统，因为windows系统默认编码为GBK
     * @return bool
     */
    public function is_win()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) == "WIN";
    }

    /**
     * 根据文件的扩展名返回文件的MINE类型
     *
     * @param $fileName string 带扩展名的文件名
     *
     * @return string
     */
    public function get_mimetype($fileName)
    {
        $mime_types = [
            'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx'    => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam'    => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb'    => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'apk'     => 'application/vnd.android.package-archive',
            'hqx'     => 'application/mac-binhex40',
            'cpt'     => 'application/mac-compactpro',
            'doc'     => 'application/msword',
            'ogg'     => 'application/ogg',
            'pdf'     => 'application/pdf',
            'rtf'     => 'text/rtf',
            'mif'     => 'application/vnd.mif',
            'xls'     => 'application/vnd.ms-excel',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'odc'     => 'application/vnd.oasis.opendocument.chart',
            'odb'     => 'application/vnd.oasis.opendocument.database',
            'odf'     => 'application/vnd.oasis.opendocument.formula',
            'odg'     => 'application/vnd.oasis.opendocument.graphics',
            'otg'     => 'application/vnd.oasis.opendocument.graphics-template',
            'odi'     => 'application/vnd.oasis.opendocument.image',
            'odp'     => 'application/vnd.oasis.opendocument.presentation',
            'otp'     => 'application/vnd.oasis.opendocument.presentation-template',
            'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'odt'     => 'application/vnd.oasis.opendocument.text',
            'odm'     => 'application/vnd.oasis.opendocument.text-master',
            'ott'     => 'application/vnd.oasis.opendocument.text-template',
            'oth'     => 'application/vnd.oasis.opendocument.text-web',
            'sxw'     => 'application/vnd.sun.xml.writer',
            'stw'     => 'application/vnd.sun.xml.writer.template',
            'sxc'     => 'application/vnd.sun.xml.calc',
            'stc'     => 'application/vnd.sun.xml.calc.template',
            'sxd'     => 'application/vnd.sun.xml.draw',
            'std'     => 'application/vnd.sun.xml.draw.template',
            'sxi'     => 'application/vnd.sun.xml.impress',
            'sti'     => 'application/vnd.sun.xml.impress.template',
            'sxg'     => 'application/vnd.sun.xml.writer.global',
            'sxm'     => 'application/vnd.sun.xml.math',
            'sis'     => 'application/vnd.symbian.install',
            'wbxml'   => 'application/vnd.wap.wbxml',
            'wmlc'    => 'application/vnd.wap.wmlc',
            'wmlsc'   => 'application/vnd.wap.wmlscriptc',
            'bcpio'   => 'application/x-bcpio',
            'torrent' => 'application/x-bittorrent',
            'bz2'     => 'application/x-bzip2',
            'vcd'     => 'application/x-cdlink',
            'pgn'     => 'application/x-chess-pgn',
            'cpio'    => 'application/x-cpio',
            'csh'     => 'application/x-csh',
            'dvi'     => 'application/x-dvi',
            'spl'     => 'application/x-futuresplash',
            'gtar'    => 'application/x-gtar',
            'hdf'     => 'application/x-hdf',
            'jar'     => 'application/x-java-archive',
            'jnlp'    => 'application/x-java-jnlp-file',
            'js'      => 'application/x-javascript',
            'ksp'     => 'application/x-kspread',
            'chrt'    => 'application/x-kchart',
            'kil'     => 'application/x-killustrator',
            'latex'   => 'application/x-latex',
            'rpm'     => 'application/x-rpm',
            'sh'      => 'application/x-sh',
            'shar'    => 'application/x-shar',
            'swf'     => 'application/x-shockwave-flash',
            'sit'     => 'application/x-stuffit',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc'  => 'application/x-sv4crc',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-tex',
            'man'     => 'application/x-troff-man',
            'me'      => 'application/x-troff-me',
            'ms'      => 'application/x-troff-ms',
            'ustar'   => 'application/x-ustar',
            'src'     => 'application/x-wais-source',
            'zip'     => 'application/zip',
            'm3u'     => 'audio/x-mpegurl',
            'ra'      => 'audio/x-pn-realaudio',
            'wav'     => 'audio/x-wav',
            'wma'     => 'audio/x-ms-wma',
            'wax'     => 'audio/x-ms-wax',
            'pdb'     => 'chemical/x-pdb',
            'xyz'     => 'chemical/x-xyz',
            'bmp'     => 'image/bmp',
            'gif'     => 'image/gif',
            'ief'     => 'image/ief',
            'png'     => 'image/png',
            'wbmp'    => 'image/vnd.wap.wbmp',
            'ras'     => 'image/x-cmu-raster',
            'pnm'     => 'image/x-portable-anymap',
            'pbm'     => 'image/x-portable-bitmap',
            'pgm'     => 'image/x-portable-graymap',
            'ppm'     => 'image/x-portable-pixmap',
            'rgb'     => 'image/x-rgb',
            'xbm'     => 'image/x-xbitmap',
            'xpm'     => 'image/x-xpixmap',
            'xwd'     => 'image/x-xwindowdump',
            'css'     => 'text/css',
            'rtx'     => 'text/richtext',
            'tsv'     => 'text/tab-separated-values',
            'jad'     => 'text/vnd.sun.j2me.app-descriptor',
            'wml'     => 'text/vnd.wap.wml',
            'wmls'    => 'text/vnd.wap.wmlscript',
            'etx'     => 'text/x-setext',
            'mxu'     => 'video/vnd.mpegurl',
            'flv'     => 'video/x-flv',
            'wm'      => 'video/x-ms-wm',
            'wmv'     => 'video/x-ms-wmv',
            'wmx'     => 'video/x-ms-wmx',
            'wvx'     => 'video/x-ms-wvx',
            'avi'     => 'video/x-msvideo',
            'movie'   => 'video/x-sgi-movie',
            'ice'     => 'x-conference/x-cooltalk',
            '3gp'     => 'video/3gpp',
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'aifc'    => 'audio/x-aiff',
            'aiff'    => 'audio/x-aiff',
            'asc'     => 'text/plain',
            'atom'    => 'application/atom+xml',
            'au'      => 'audio/basic',
            'bin'     => 'application/octet-stream',
            'cdf'     => 'application/x-netcdf',
            'cgm'     => 'image/cgm',
            'class'   => 'application/octet-stream',
            'dcr'     => 'application/x-director',
            'dif'     => 'video/x-dv',
            'dir'     => 'application/x-director',
            'djv'     => 'image/vnd.djvu',
            'djvu'    => 'image/vnd.djvu',
            'dll'     => 'application/octet-stream',
            'dmg'     => 'application/octet-stream',
            'dms'     => 'application/octet-stream',
            'dtd'     => 'application/xml-dtd',
            'dv'      => 'video/x-dv',
            'dxr'     => 'application/x-director',
            'eps'     => 'application/postscript',
            'exe'     => 'application/octet-stream',
            'ez'      => 'application/andrew-inset',
            'gram'    => 'application/srgs',
            'grxml'   => 'application/srgs+xml',
            'gz'      => 'application/x-gzip',
            'htm'     => 'text/html',
            'html'    => 'text/html',
            'ico'     => 'image/x-icon',
            'ics'     => 'text/calendar',
            'ifb'     => 'text/calendar',
            'iges'    => 'model/iges',
            'igs'     => 'model/iges',
            'jp2'     => 'image/jp2',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'kar'     => 'audio/midi',
            'lha'     => 'application/octet-stream',
            'lzh'     => 'application/octet-stream',
            'm4a'     => 'audio/mp4a-latm',
            'm4p'     => 'audio/mp4a-latm',
            'm4u'     => 'video/vnd.mpegurl',
            'm4v'     => 'video/x-m4v',
            'mac'     => 'image/x-macpaint',
            'mathml'  => 'application/mathml+xml',
            'mesh'    => 'model/mesh',
            'mid'     => 'audio/midi',
            'midi'    => 'audio/midi',
            'mov'     => 'video/quicktime',
            'mp2'     => 'audio/mpeg',
            'mp3'     => 'audio/mpeg',
            'mp4'     => 'video/mp4',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpga'    => 'audio/mpeg',
            'msh'     => 'model/mesh',
            'nc'      => 'application/x-netcdf',
            'oda'     => 'application/oda',
            'ogv'     => 'video/ogv',
            'pct'     => 'image/pict',
            'pic'     => 'image/pict',
            'pict'    => 'image/pict',
            'pnt'     => 'image/x-macpaint',
            'pntg'    => 'image/x-macpaint',
            'ps'      => 'application/postscript',
            'qt'      => 'video/quicktime',
            'qti'     => 'image/x-quicktime',
            'qtif'    => 'image/x-quicktime',
            'ram'     => 'audio/x-pn-realaudio',
            'rdf'     => 'application/rdf+xml',
            'rm'      => 'application/vnd.rn-realmedia',
            'roff'    => 'application/x-troff',
            'sgm'     => 'text/sgml',
            'sgml'    => 'text/sgml',
            'silo'    => 'model/mesh',
            'skd'     => 'application/x-koan',
            'skm'     => 'application/x-koan',
            'skp'     => 'application/x-koan',
            'skt'     => 'application/x-koan',
            'smi'     => 'application/smil',
            'smil'    => 'application/smil',
            'snd'     => 'audio/basic',
            'so'      => 'application/octet-stream',
            'svg'     => 'image/svg+xml',
            't'       => 'application/x-troff',
            'texi'    => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'tr'      => 'application/x-troff',
            'txt'     => 'text/plain',
            'vrml'    => 'model/vrml',
            'vxml'    => 'application/voicexml+xml',
            'webm'    => 'video/webm',
            'wrl'     => 'model/vrml',
            'xht'     => 'application/xhtml+xml',
            'xhtml'   => 'application/xhtml+xml',
            'xml'     => 'application/xml',
            'xsl'     => 'application/xml',
            'xslt'    => 'application/xslt+xml',
            'xul'     => 'application/vnd.mozilla.xul+xml',
        ];
        $extArr = explode('.', $fileName);
        $ext = array_pop($extArr);
        $ext = strtolower($ext);

        return (isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream');
    }
}
