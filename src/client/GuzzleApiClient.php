<?php
/**
 * @author ihipop@gmail.com @ 19-4-9 上午11:13 For youzan-sdk.
 */

namespace ihipop\Youzan\client;

use Psr\Http\Message\RequestInterface;

class GuzzleApiClient extends ApiClient
{

    /** @var $httpClient \GuzzleHttp\Client */
    protected $httpClient;

    public function send(RequestInterface $request)
    {
        $response = $this->httpClient->send($request);

        return $this->parseResponse($response);
    }
}