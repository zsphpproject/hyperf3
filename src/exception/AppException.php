<?php

namespace Zsgogo\exception;

use Hyperf\Server\Exception\ServerException;
use Throwable;
use App\common\constant\ErrorNums;

class AppException extends ServerException {

    public function __construct(int $code = ErrorNums::SYS_ERROR, string $message = null, Throwable $previous = null) {
        if (is_null($message)) {
            $message = ErrorNums::getInstance()->getMessage($code);
        }
        parent::__construct($message, $code, $previous);
    }
}