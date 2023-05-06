<?php

namespace Zsgogo\utils;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class Log {

    /**
     * 日志
     * @param string $name
     * @return LoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function get(string $name = 'app'): LoggerInterface {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}