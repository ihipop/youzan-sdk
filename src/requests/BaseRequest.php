<?php

namespace ihipop\Youzan\requests;

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use ihipop\Youzan\utility\Str;
use function GuzzleHttp\Psr7\stream_for;

abstract class BaseRequest
{

    protected $uriPlaceHolder = [];
    protected $method         = 'POST';
    public    $apiBase        = 'https://open.youzan.com/api/oauthentry';
    protected $apiPath        = '/{apiShortName}/{apiVersion}/{apiAction}';
    public    $data;
    public    $query          = [];
    public    $userAgent      = 'YouZan-PHP-SDK/0.1';
    protected $contentType    = 'form';
    protected $apiName;
    protected $apiVersion     = '3.0.0';
    public    $accessToken;

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     *
     * @return BaseRequest
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param string $apiVersion
     *
     * @return BaseRequest
     */
    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion                     = $apiVersion;
        $this->uriPlaceHolder['{apiVersion}'] = $this->apiVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function __construct()
    {
        if (strpos($this->apiPath, '{') !== false) {
            preg_replace_callback('/\{.*?\}/', function ($matches) {
                $this->uriPlaceHolder[$matches[0]] = null;
            }, $this->apiPath);
        }
        $this->uriPlaceHolder['{apiVersion}'] = $this->apiVersion;
        if (!$this->apiName) {
            //如果没有定义 $this->apiName 那么把 \xxx\yyy\zzz\requests\GetTradesSold
            //处理为 youzan.trades.sold.get
            $namespaceArray = explode('\\', get_called_class());
            $class          = end($namespaceArray);
            $lastNamespace  = 'youzan';
            $class          = explode('_', Str::snake($class, '_'));
            $class[]        = array_shift($class);//把动作名词放尾部

            $this->apiName = ($lastNamespace ? ($lastNamespace . '.') : '') . implode('.', $class);
        }

        $class = explode('.', $this->apiName);

        $this->uriPlaceHolder['{apiShortName}'] = implode('.', array_slice($class, 0, -1));
        $this->uriPlaceHolder['{apiAction}']    = array_slice($class, -1)[0];
    }


    public static function maybeApiRequestDsn(string $string): bool
    {
        return strpos($string,'youzan.') === 0;
    }

    public function setData($value, $merge = false)
    {
        if (!$this->contentType) {
            throw  new  \Exception('未指定请求类型');
        }
        if (is_array($value) && is_array($this->data) && $merge) {
            $this->data = array_merge($this->data, $value);
        } else {
            $this->data = $value;
        }

        return $this;
    }

    public function setQuery(array $value, $merge = false)
    {

        $this->query = $merge ? array_merge($this->query, $value) : $value;

        return $this;
    }

    public function getApiPath()
    {
        if ($this->uriPlaceHolder && strpos($this->apiPath, '{') !== false ) {
            return str_replace(array_keys($this->uriPlaceHolder), array_values($this->uriPlaceHolder), $this->apiPath);
        }

        return $this->apiPath;
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {

        $uri = $this->apiBase . $this->getApiPath();
        //        var_export($uri);
        $uri   = new Uri($uri);
        $data  = $this->data;
        $query = $this->query;

        if ($this->accessToken) {
            if ($this->method === 'GET') {
                $query['access_token'] = $this->accessToken;
            } else {
                $data['access_token'] = $this->accessToken;
            }
        }

        if ($query) {
            $uri = $uri->withQuery(http_build_query($this->query));
        }

        $request = (new Request($this->method, $uri))
            ->withHeader('user-agent', $this->userAgent);

        $contentType = $this->contentType;
        if ($data && $contentType) {
            if ($contentType === 'form') {
                $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8')
                    ->withBody(stream_for(is_array($data) ? http_build_query($data) : $data));
            } elseif ($contentType === 'multipart') {
                $stream  = new MultipartStream($data);
                $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $stream->getBoundary())
                    ->withBody($stream);
            } elseif ($contentType === 'json') {
                $request = $request->withHeader('Content-Type', 'application/json')
                    ->withBody(is_string($data) ?: stream_for(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
            }
        }

        return $request;
    }
}