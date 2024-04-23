<?php
declare(strict_types=1);

namespace Zsgogo\utils;

use Hyperf\Context\Context;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Request\FormRequest;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

abstract class Pojo extends FormRequest
{

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * @var array the keys to identify the data of request in coroutine context
     */
    protected array $contextkeys
        = [
            'parsedData' => 'pojo.parsedData',
        ];

    public function __construct(protected ContainerInterface $container)
    {
        $this->reflectionClass = new ReflectionClass($this);
        parent::__construct($container);
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
            $inputData = $this->input($propertySnakeName);
            if ('' === $inputData || null === $inputData) {
                $data[$propertySnakeName] = $property->getDefaultValue();
            } else {
                $data[$propertySnakeName] = $inputData;
            }
        }
        return $data;
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
     * @throws ReflectionException
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'get')) {
            $funcName = Str::after($name, 'get');
            $value = $this->input(Str::snake(Str::after($name, 'get')),'');
            if ('' === $value) {
                return $this->reflectionClass->getProperty(lcfirst($funcName))->getDefaultValue();
            }
            return $value;
        }
        if (Str::startsWith($name, 'set')) {
            $this->updateParsedData(Str::snake(Str::after($name, 'set')), ...$arguments);
            return true;
        }
        throw new RuntimeException($name. ' Method not exist.');
    }

    /**
     * @param mixed $key
     * @param mixed|null $data
     * @return void
     */
    protected function updateParsedData(mixed $key, mixed $data = null): void
    {
        Context::override($this->contextkeys['parsedData'], function ($old)use ($key, $data) {
            if (is_array($key)) {
                $updateData = $key;
            } else {
                $updateData = [$key => $data];
            }
            return array_merge($old ?? [], $updateData);
        });
    }

    public function authorize(): bool
    {
        return true;
    }
}
