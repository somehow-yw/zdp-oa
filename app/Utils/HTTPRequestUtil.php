<?php

namespace App\Utils;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use App\Exceptions\HTTPRequest\BadResponseException;

/**
 * HTTP Request utils
 *
 * Usage: you should injection this class object into the code
 *        where need trigger a http request.
 * example:
 *      class TestService
 *      {
 *          public function __construct(HTTPRequestUtil $httpClient)
 *          {
 *              $this->httpClient = $httpClient;
 *          }
 *
 *          public function test()
 *          {
 *              $responseBody = $this->httpClient->get('http://www.163.com', ['user' => 'zhangsan']);
 *              // process body
 *          }
 *      }
 */
class HTTPRequestUtil
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    public function __construct(
        ClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Trigger a get http request
     *
     * @param string $url
     * @param array  $querys get request params
     * @param array  $headers
     *
     * @return string $respContent
     */
    public function get($url, array $querys = [], array $headers = [])
    {
        $options = [
            RequestOptions::QUERY => $querys,
        ];
        if ($headers) {
            $options[RequestOptions::HEADERS] = $headers;
        }

        return $this->rawGet($url, $options);
    }

    /**
     * Trigger a get http request
     *
     * @param string $url
     * @param array  $querys get request params
     * @param array  $headers
     *
     * @return string $respContent
     */
    public function json($url, array $querys = [], array $headers = [])
    {
        $options = [
            RequestOptions::JSON => $querys,
        ];
        if ($headers) {
            $options[RequestOptions::HEADERS] = $headers;
        }

        return $this->rawPost($url, $options);
    }

    /**
     * Trigger a post http request
     *
     * @param string $url
     * @param array  $formData
     * @param array  $headers
     *
     * @return string $respContent
     */
    public function post($url, $formData = [], array $headers = [])
    {
        $options = [
            RequestOptions::FORM_PARAMS => $formData,
        ];
        if ($headers) {
            $options[RequestOptions::HEADERS] = $headers;
        }

        return $this->rawPost($url, $options);
    }

    public function rawGet($url, $options = [])
    {
        $response = $this->httpClient->request('GET', $url, $options);
        if ($response->getStatusCode() != 200) {
            throw new BadResponseException(
                sprintf('http请求失败(%s): %s', $response->getStatusCode(), (string)$response->getBody())
            );
        }

        return (string)$response->getBody();
    }

    public function rawPost($url, $options = [])
    {
        $response = $this->httpClient->request('POST', $url, $options);
        if ($response->getStatusCode() != 200) {
            throw new BadResponseException(
                sprintf('http请求失败(%s): %s', $response->getStatusCode(), (string)$response->getBody())
            );
        }

        return (string)$response->getBody();
    }
}
