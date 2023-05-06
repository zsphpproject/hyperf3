<?php
declare(strict_types=1);

namespace Zsgogo\constant;

/**
 * 错误码
 * Class ErrorNums
 * @package ChengYi\constant
 */
class ErrorNums
{
    /**
     * @var ErrorNums
     */
    private static $_instance;

    const SUCCESS = 0;

    const SYS_ERROR = 10001; // 系统未知错误

    const DB_ERROR = 20001; // 数据库错误
    const SSO_ERROR = 20008;
    const GUZZLE_REQUEST_FAIL = 20009;


    const FREQUENT_REQUEST = 400000;
    const INVALID_AUTH = 400001;
    const NO_ACCESS = 400002;
    const ACCOUNT_ERROR = 400102;
    const Unauthorized = 400003;  // 参数错误
    const METHOD_NOT_EXISTS = 400006; // 方法不存在
    const METHOD_NOT_PUBLIC = 400007; // 方法非共有

    const USERNAME_IS_EXIST = 400100;
    const PHONE_IS_EXIST = 400101;
    const CREATE_ERROR = 400201;
    const DELETE_ERROR = 400202;
    const TARGET_NO_EXIST = 400203;
    const USER_HAS_DISABLE = 400204;
    const UNBOUNDED_MODIFICATION = 400205;
    const REPEAT_SUBMIT = 400206;
    const STATUS_ERROR = 400207;



    private array $message = [
        ErrorNums::SYS_ERROR => "系统未知错误",
        ErrorNums::DB_ERROR => "db error",
        ErrorNums::SSO_ERROR => "sso error",
        ErrorNums::FREQUENT_REQUEST => "请求频繁",
        ErrorNums::ACCOUNT_ERROR => "账号或密码错误",
        ErrorNums::Unauthorized => "参数有误",
        ErrorNums::METHOD_NOT_EXISTS => "方法不存在",
        ErrorNums::METHOD_NOT_PUBLIC => "方法非共有",
        ErrorNums::USERNAME_IS_EXIST => "账号已存在",
        ErrorNums::PHONE_IS_EXIST => "手机号已存在",
        ErrorNums::INVALID_AUTH => "身份校验失败",
        ErrorNums::NO_ACCESS => "无权访问",
        ErrorNums::CREATE_ERROR => "添加失败",
        ErrorNums::DELETE_ERROR => "删除失败",
        ErrorNums::TARGET_NO_EXIST => "目标不存在",
        ErrorNums::USER_HAS_DISABLE => "您的账号已被禁用，请与管理员联系处理",
        ErrorNums::UNBOUNDED_MODIFICATION => "无权修改",
        ErrorNums::REPEAT_SUBMIT => "请勿重复提交",
        ErrorNums::STATUS_ERROR => "状态有误",
        ErrorNums::GUZZLE_REQUEST_FAIL => "请求失败"
    ];


    public static function getInstance(): ErrorNums {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getMessage(int $code): string {
        return $this->message[$code] ?? '未知错误';
    }
}
