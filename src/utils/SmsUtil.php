<?php

namespace Zsgogo\utils;

use App\common\constant\ErrorNums;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\PhoneNumber;
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
     * @return array
     */
    public function send(string $mobile,string $code, string $template,string $content = ""): array {
        $easySms = new EasySms($this->config);
        try {
            return $easySms->send($mobile, ['content' => $content ?? '您的验证码为: ' . $code, 'template' => $template, 'data' => ['code' => $code],]);
        } catch (InvalidArgumentException|NoGatewayAvailableException $e) {
            throw new AppException(ErrorNums::EXECUTION_ERROR,json_encode($e->getExceptions()));
        }
    }

    /**
     * 发送验证码-国际
     * @param string $mobile
     * @param string $areaCode
     * @param string $code
     * @param string $template
     * @param string $content
     * @return array
     */
    public function sendInternational(string $mobile,string $areaCode,string $code, string $template,string $content = "") :array {
        $number = new PhoneNumber($mobile, $areaCode);
        $easySms = new EasySms($this->config);
        try {
            return $easySms->send($number, ['content' => $content ?? '您的验证码为: ' . $code, 'template' => $template, 'data' => ['code' => $code],]);
        } catch (InvalidArgumentException|NoGatewayAvailableException $e) {
            throw new AppException(ErrorNums::EXECUTION_ERROR,json_encode($e->getExceptions()));
        }
    }
}