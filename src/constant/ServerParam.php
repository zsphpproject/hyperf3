<?php

namespace Zsgogo\constant;

use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Zsgogo\utils\Pojo;

Class ServerParam extends Pojo {
    public function __construct(RequestInterface $request, array $param = []) {
        parent::__construct($request, $param);
        if (isset($param["request_id"]) && $param["request_id"] != "") Context::set("request_id",$param["request_id"]);
    }
}