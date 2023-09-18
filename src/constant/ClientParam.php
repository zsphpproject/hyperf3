<?php

namespace Zsgogo\constant;

use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Zsgogo\utils\Pojo;
use Zsgogo\utils\SnowFlakeUtil;

class ClientParam extends Pojo {
    public function __construct(RequestInterface $request, array $param = []) {
        $param["request_id"] = Context::get("request_id");
        parent::__construct($request, $param);
    }
}