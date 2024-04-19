<?php
declare(strict_types=1);

namespace Zsgogo\utils;

use Hyperf\Context\Context;
use Hyperf\Contract\Arrayable;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionProperty;
use App\common\constant\ErrorNums;
use RuntimeException;


abstract class Pojo implements Arrayable
{

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * @var array the keys to identify the data of request in coroutine context
     */
    protected array $contextKeys
        = [
            'parsedData' => 'pojo.parsedData',
        ];

    public function __construct()
    {
        $this->reflectionClass = new ReflectionClass($this);
    }

    /**
     * 根据对象设置的属性转为数组
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            $data[$propertySnakeName] = $this->input($propertySnakeName);
        }
        return $data;
    }

    /**
     * 原始入参，可能会包含merge的数据
     * @return array
     */
    public function all(): array
    {
        return $this->getInputData();
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->getInputData();
        return \Hyperf\Collection\data_get($data, $key, $default);
    }

    /**
     * 将另外一个数字合并到该对象中
     * @param array $mergeData
     * @return void
     */
    public function mergeData(array $mergeData): void
    {
        $this->updateParsedData($mergeData);
    }

    /**
     * getter / setter
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'get')) {
            return $this->input(Str::snake(Str::after($name, 'get')));
        }
        if (Str::startsWith($name, 'set')) {
            $this->updateParsedData(Str::snake(Str::after($name, 'set')), ...$arguments);
            return true;
        }
        throw new RuntimeException($name. ' Method not exist.');
    }

    /**
     * @return ServerRequestInterface
     */
    private function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }

    /**
     * @return array
     */
    private function getInputData(): array
    {
        return $this->storeParsedData(function () {
            $request = $this->getRequest();
            if (is_array($request->getParsedBody())) {
                $data = $request->getParsedBody();
            } else {
                $data = [];
            }
            return array_merge($data, $request->getQueryParams());
        });
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    private function storeParsedData(callable $callback): mixed
    {
        if (!Context::has($this->contextKeys['parsedData'])) {
            return Context::set($this->contextKeys['parsedData'], $callback());
        }
        return Context::get($this->contextKeys['parsedData']);
    }

    /**
     * @param mixed $key
     * @param mixed|null $data
     * @return void
     */
    private function updateParsedData(mixed $key, mixed $data = null): void
    {
        Context::override($this->contextKeys['parsedData'], function ($old)use ($key, $data) {
            if (is_array($key)) {
                $updateData = $key;
            } else {
                $updateData = [$key => $data];
            }
            return array_merge($old ?? [], $updateData);
        });
    }
}
