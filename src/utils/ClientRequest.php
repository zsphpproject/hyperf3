<?php

namespace Zsgogo\utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use App\common\constant\ErrorNums;
use Zsgogo\exception\AppException;

class ClientRequest {

    /**
     * 发送请求
     * @param string $baseUrl 接口跟路径
     * @param string $path 接口uri
     * @param string $method
     * @param array $param 参数
     * @param array $headers
     * @param string $request_id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function send(string $baseUrl,string $path = "",string $method = "post",array $param = [],array $headers = [],string $request_id = ""): mixed {
        $client = new Client([
            "base_uri" => $baseUrl,
            "timeout" => 10.0
        ]);

        $option[RequestOptions::JSON] = [];
        if (!empty($param)) $option[RequestOptions::JSON] = $param;
        if (!empty($headers)) $option[RequestOptions::HEADERS] = $headers;

        Log::get("guzzle_request")->info(json_encode($option,JSON_UNESCAPED_UNICODE),["request_id" => $request_id]);

        try {
            $response = $client->request($method, $path, $option);
            $res = json_decode($response->getBody()->getContents(),true);
            Log::get("guzzle_response")->info(json_encode($res,JSON_UNESCAPED_UNICODE),["request_id" => $request_id]);

            if ($response->getStatusCode() == 200){
                if (isset($res["code"]) && $res["code"] != 0) {
                    Log::get("guzzle_response")->info("{$path}请求错误:" . $res["msg"],["request_id" => $request_id]);
                    throw new AppException(ErrorNums::SERVER_ERROR,$res["msg"]);
                }
                if (isset($res["status"]) && $res["status"] != 0){
                    Log::get("guzzle_response")->info("{$path}请求错误:" . $res["message"],["request_id" => $request_id]);
                    throw new AppException(ErrorNums::SERVER_ERROR,$res["message"]);
                }
                return $res["data"];
            }else{
                $message = $response->getBody()->getContents();
                Log::get("guzzle_response")->info("{$path}请求失败:" . $message,["request_id" => $request_id]);
                throw new AppException(ErrorNums::SERVER_ERROR,$message);
            }
        }catch (GuzzleException $exception){
            $error = $exception->getMessage();
            $str = "Client error: `POST ".env("SHOP_BASE_URL") . $path ."` resulted in a `".$exception->getCode()." Bad Request` response:";
            $error2 = Str::replace($str,"",$error);
            $error2 = json_decode($error2,true);
            Log::get("guzzle_response")->info("{$path}求失败:" . $error2["message"],["request_id" => $request_id]);
            throw new AppException(ErrorNums::SERVER_ERROR,$error2["message"] ?? "请求应用未知错误");
        }
    }



}