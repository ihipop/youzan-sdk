<?php

namespace ihipop\Youzan;

use GuzzleHttp\Psr7\Request;

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
            return $body;
        }
        throw new \Exception($message ?? '未知错误', 999);
    }
}