<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\InvalidArgumentException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Zsgogo\exception\AppException;

class SmsUtil {

    private array $config;

    public function __construct(array $config = []) {
        $this->config = !empty($config) ? $config : config('sms');
    }

    /**
     * 发送验证码
     * @param string $mobile
     * @param string $code
     * @param string $template
     * @param string $content
     * @return void
     */
    public function send(string $mobile,string $code, string $template,string $content = ""): void {
        $easySms = new EasySms($this->config);
        try {
            $easySms->send($mobile, ['content' => $content ?? '您的验证码为: ' . $code, 'template' => $template, 'data' => ['code' => $code],]);
        } catch (InvalidArgumentException|NoGatewayAvailableException $e) {
            throw new AppException(ErrorNums::EXECUTION_ERROR,json_encode($e->getExceptions()));
        }
    }


}