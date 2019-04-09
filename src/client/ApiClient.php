<?php

namespace ihipop\Youzan\client;

use ihipop\Youzan\Application;
use ihipop\Youzan\exceptions\TokenInvalidException;
use ihipop\Youzan\exceptions\YouzanServerSideException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ApiClient
{

    protected $httpClient;
    protected $app;

    public function __construct(Application $app)
    {
        $this->httpClient = $app['http_client'];
        $this->app        = $app;
    }

    /**
     * @param \GuzzleHttp\Psr7\Request $request
     *
     * @return mixed
     */
    abstract public function send(RequestInterface $request);

    public function parseResponse(ResponseInterface $response)
    {
        $body = json_decode((string)$response->getBody(), true) ?: [];
        // 有赞有些接口中返回的错误信息包含在msg/message属性
        $message = $body['error_response']['sub_msg'] ?? ($body['error_response']['msg'] ?? ($body['error_response']['message'] ?? null));
        $code    = $body['error_response']['sub_code'] ?? ($body['error_response']['code'] ?? null);

        if (!$message) {
            return $body['response'];
        }
        throw  $this->getExceptionInstanceBycode($code, $message);
    }

    public function getExceptionClassBycode($code)
    {
        switch ($code) {
            case 10000:
            case 10001:
            case 40009:
            case 40010:
                return TokenInvalidException::class;
            default:
                return YouzanServerSideException::class;
        }
    }

    public function getExceptionInstanceBycode($code, $message)
    {
        if (!is_int($code)) {
            $code = -1;
        }
        $class = $this->getExceptionClassBycode($code);

        return new $class($message ?? '未知错误', $code);
    }
}