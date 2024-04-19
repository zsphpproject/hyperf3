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
use Zsgogo\exception\AppException;


abstract class Pojo implements Arrayable
{

    /**
     * @var array $data 数据
     */
    private $data = [];

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var array the keys to identify the data of request in coroutine context
     */
    protected array $contextkeys
        = [
            'parsedData' => 'http.request.parsedData',
        ];

    public function __construct(array $param = [])
    {
        $inputData = $this->getInputData();
        if (!empty($param)) {
            $inputData = array_merge($inputData, $param);
        }
        $this->reflectionClass = new ReflectionClass($this);
        $this->setData($inputData);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if (!isset($this->data[$propertySnakeName])) {
                $getter = \Hyperf\Support\getter($propertySnakeName);
                $this->data[$propertySnakeName] = $this->$getter();
            }
        }
        return $this->data;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->getInputData();
    }

    /**
     * @throws AppException
     */
    private function setData($inputData): void
    {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertySnakeName = Str::snake($property->getName());
            if (isset($inputData[$propertySnakeName])) {
                $propertyValue = $inputData[$propertySnakeName] != "" ? $inputData[$propertySnakeName] : $property->getDefaultValue();
                $property->setValue($this, $propertyValue);
                $this->data[$propertySnakeName] = $propertyValue;
            }
        }
    }

    /**
     * @return ServerRequestInterface
     */
    protected function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    protected function call($name, $arguments)
    {
        $request = $this->getRequest();
        if (! method_exists($request, $name)) {
            throw new RuntimeException('Method not exist.');
        }
        return $request->{$name}(...$arguments);
    }

    /**
     * @return array
     */
    protected function getInputData(): array
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
    protected function storeParsedData(callable $callback): mixed
    {
        if (! Context::has($this->contextkeys['parsedData'])) {
            return Context::set($this->contextkeys['parsedData'], $callback());
        }
        return Context::get($this->contextkeys['parsedData']);
    }
}
