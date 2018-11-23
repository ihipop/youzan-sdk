<?php

namespace ihipop\Youzan\requests;

class Request extends BaseRequest
{
    public function __construct(string  $apiName)
    {
        if(!$apiName){
            throw new \RuntimeException('RAW Request 必须指定合法API名称');
        }
        $this->apiName = $apiName;
        parent::__construct();
    }
}