<?php
/**
 * @author ihipop@gmail.com @ 19-2-28 下午2:26 For youzan-sdk.
 */

namespace ihipop\Youzan\providers;

use GuzzleHttp\Client;
use ihipop\Youzan\Application;
use ihipop\Youzan\client\GuzzleApiClient;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GuzzleApiClientServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['http_client'] = function (Application $app) {
            return new Client($app->getConfig('http.guzzle_config'));
        };
        $pimple['api_client']  = function (Application $app) {
            return $client = new GuzzleApiClient($app);
        };
    }
}