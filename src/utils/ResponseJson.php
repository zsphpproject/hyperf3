<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;


class ResponseJson {

    /**
     * @param $data
     * @param string $msg
     * @return array
     */
    public static function success($data = null, string $msg = "success"): array {
        return [
            "code" => ErrorNums::SUCCESS,
            "msg" => $msg,
            "request_id" => SnowFlakeUtil::getInstance()->getCurrentId(),
            "data" => $data
        ];
    }

    /**
     * @param int $code
     * @param string $msg
     * @param $data
     * @return array
     */
    public static function fail(int $code = ErrorNums::SYS_ERROR,string $msg = "",$data = null): array {
        return [
            "code" => $code,
            "msg" => $msg ?: ErrorNums::getInstance()->getMessage($code),
            "request_id" => SnowFlakeUtil::getInstance()->getCurrentId(),
            "data" => $data
        ];

    }



    // public static function error(int $code = ErrorNums::SYS_ERROR, string $msg = "", int $statusCode = 400): Response {
    //     return Response::create([
    //         "code" => ErrorNums::Unauthorized,
    //         "msg" => $msg,
    //         "request_id" => SnowFlakeUtil::getInstance()->getCurrentId(),
    //         "data" => null
    //     ],"json",$statusCode);
    // }
}