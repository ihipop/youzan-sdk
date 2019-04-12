<?php
/**
 * @author ihipop@gmail.com @ 19-2-27 下午6:16 For youzan-sdk.
 */

namespace ihipop\Youzan;

use ihipop\Youzan\providers\GuzzleApiClientServiceProvider;
use ihipop\Youzan\utility\Arr;
use Pimple\Container;

/**
 * Class Application
 *
 * @package ihipop\alibaba
 *
 * @property  \ihipop\Youzan\client\ApiClient api_client
 */
class Application extends Container implements \Psr\Container\ContainerInterface
{

    protected $defaultConfig = [];
    /**
     * @var array
     */
    protected $userConfig = [];
    protected $providers  = [];

    /**
     * Constructor.
     *
     * @param array       $config
     * @param array       $prepends
     * @param string|null $id
     */
    public function __construct(array $config = [], array $prepends = [])
    {
        $this->userConfig = $config;
        $this->registerProviders($this->getProviders());
        parent::__construct($prepends);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return array_merge([
            $this->getConfig('http.provider'),
        ], $this->providers);
    }

    /**
     * @return array
     */
    public function getConfig(string $key = null)
    {
        $base = [
            // http://docs.guzzlephp.org/en/stable/request-options.html
            'http'       => [
                'provider' => GuzzleApiClientServiceProvider::class,
                'guzzle_config'   => [
                    'verify'          => false,
                    'timeout'         => 30,
                    'connect_timeout' => 30,
                ],
                'saber_config'   => [
                    'use_pool'          => true,
                ],
            ],
            'request'    => [
                'prop' => [
                    'apiBase' => 'https://open.youzan.com/api/oauthentry',
                ],
            ],
            'api_client' => [
                'apiKey'    => '123456',
                'apiSecret' => 'qwerty.',
            ],
        ];

        $config = array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
        if ($key) {
            return Arr::get($config, $key, []);
        }

        return $config;
    }

    public function get($id)
    {
        return $this[$id];
    }

    public function has($id)
    {
        return isset($this[$id]);
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }
}