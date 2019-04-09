<?php
/**
 * @author ihipop@gmail.com @ 19-4-9 上午11:17 For youzan-sdk.
 */

namespace ihipop\Youzan\client;

use Psr\Http\Message\RequestInterface;
use Swlib\SaberGM;

class SaberApiClient extends ApiClient
{

    /** @var $httpClient \Swlib\Saber */
    protected $httpClient;

    public function send(RequestInterface $request)
    {
        $psr = SaberGM::psr();

        $psr = $psr->withMethod($request->getMethod());
        $psr = $psr->withUri($request->getUri());
        $psr = $psr->withHeaders($request->getHeaders());
        $psr = $psr->withBody($request->getBody());

        return $psr->exec()->recv();
    }
}