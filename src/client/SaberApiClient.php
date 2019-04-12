<?php
/**
 * @author ihipop@gmail.com @ 19-4-9 上午11:17 For youzan-sdk.
 */

namespace ihipop\Youzan\client;

use Psr\Http\Message\RequestInterface;
use Swlib\Http\Uri;
use Swlib\SaberGM;

class SaberApiClient extends ApiClient
{

    /** @var $httpClient \Swlib\Saber */
    protected $httpClient;

    public function send(RequestInterface $request)
    {
        /** @var  $psr \Swlib\Saber\Request*/
        $psr = $this->httpClient->psr();
        $psr = $psr->withMethod($request->getMethod());
        $psr = $psr->withUri(new Uri((string)$request->getUri()));
        $psr = $psr->withHeaders($request->getHeaders());
        $psr = $psr->withBody($request->getBody());
        $response= $psr->exec()->recv();
        return $this->parseResponse($response);

    }
}