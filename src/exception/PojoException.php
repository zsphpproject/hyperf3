<?php

namespace Zsgogo\exception;

use Throwable;
use Zsgogo\constant\ErrorNums;

class PojoException extends \Exception {

    public function __construct(int $code = ErrorNums::SYS_ERROR, string $message = null, Throwable $previous = null) {
        if (is_null($message)) {
            $message = ErrorNums::getInstance()->getMessage($code);
        }
        parent::__construct($message, $code, $previous);
    }
}