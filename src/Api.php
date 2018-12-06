<?php

namespace ihipop\Youzan;

use GuzzleHttp\Psr7\Request;
use ihipop\Youzan\exceptions\TokenInvalidException;
use ihipop\Youzan\exceptions\YouzanServerSideException;

class Api
{

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client(
            [
                'verify'          => false,
                'timeout'         => 30,
                'connect_timeout' => 30,
            ]
        );
    }

    public function send(Request $request)
    {

        $response = $this->httpClient->send($request);

        $body = json_decode((string)$response->getBody(), true) ?: [];
        // 有赞有些接口中返回的错误信息包含在msg/message属性
        $message = $body['error_response']['msg'] ?? ($body['error_response']['message'] ?? null);
        if (!$message) {
            return $body['response'];
        }
        $code = $body['error_response']['code'] ?? null;
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